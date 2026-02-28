@extends('shadow-theme::layouts.app')

@section('title', (string) ($product->name ?? $product->slug ?? __('shadow-theme::shadow.products.single')))

@section('content')
    @php
        $visibilityValue = strtolower((string) ($product->visibility ?? 'public'));
        $visibilityKey = 'shadow-theme::shadow.products.visibility.'.$visibilityValue;
        $visibilityLabel = trans()->has($visibilityKey) ? __($visibilityKey) : ucfirst($visibilityValue);

        $tags = collect((array) ($product->tags ?? []))
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->values();

        $resolveAssetUrl = function (?string $value): ?string {
            $value = trim((string) $value);
            if ($value === '') {
                return null;
            }

            if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                return $value;
            }

            return \Illuminate\Support\Facades\Storage::disk(\NinjaPortal\Portal\Models\ApiProduct::$STORAGE_DISK)->url($value);
        };

        $specUrl = $resolveAssetUrl($product->swagger_url ?? null);
        $specViewerUrl = $specUrl ? route('shadow.products.spec', ['slug' => $product->slug, 'scope' => $scope]) : null;
        $integrationGuideUrl = $resolveAssetUrl($product->integration_file ?? null);
        $description = trim((string) ($product->description ?? ''));
        $shortDescription = trim((string) ($product->short_description ?? ''));
    @endphp

    <div class="mb-4">
        <a href="{{ route('shadow.products.index', ['scope' => $scope]) }}" class="link link-hover text-sm">
            {{ ($shadowTheme['isRtl'] ?? false) ? '→' : '←' }} {{ __('shadow-theme::shadow.products.back_to_products') }}
        </a>
    </div>

    <section class="mx-auto max-w-6xl space-y-6">
        <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="rounded-[1.9rem] border border-base-300/70 bg-gradient-to-br from-base-100 via-base-100 to-base-200/70 p-6 shadow-md sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="badge {{ $visibilityValue === 'private' ? 'badge-warning' : ($visibilityValue === 'draft' ? 'badge-neutral' : 'badge-success') }} badge-outline">
                                {{ $visibilityLabel }}
                            </span>
                            @if($tags->isNotEmpty())
                                @foreach($tags->take(4) as $tag)
                                    <span class="badge badge-ghost">{{ $tag }}</span>
                                @endforeach
                            @endif
                        </div>
                        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $product->name ?? $product->slug }}</h1>
                        <p class="mt-2 text-sm text-base-content/70">{{ $product->apigee_product_id ?? $product->slug }}</p>
                        @if($shortDescription !== '')
                            <p class="mt-5 max-w-3xl text-base leading-7 text-base-content/78 sm:text-lg">{{ $shortDescription }}</p>
                        @endif
                    </div>

                    @if($specUrl)
                        <a href="{{ $specUrl }}" target="_blank" rel="noreferrer" class="btn btn-primary btn-sm rounded-xl">
                            {{ __('shadow-theme::shadow.products.open_api_spec') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-[1.6rem] border border-base-300/70 bg-base-100/95 p-5 shadow-md">
                    <div class="mb-4 text-sm font-medium text-base-content/75">{{ __('shadow-theme::shadow.products.product_details') }}</div>
                    <div class="grid gap-3">
                        <div class="rounded-xl border border-base-300/70 bg-base-200/35 p-3">
                            <div class="text-xs text-base-content/60">{{ __('shadow-theme::shadow.products.visibility_label') }}</div>
                            <div class="mt-1 font-medium">{{ $visibilityLabel }}</div>
                        </div>

                        <div class="rounded-xl border border-base-300/70 bg-base-200/35 p-3">
                            <div class="text-xs text-base-content/60">{{ __('shadow-theme::shadow.products.tags_label') }}</div>
                            @if($tags->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($tags as $tag)
                                        <span class="badge badge-outline">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-1 text-sm text-base-content/70">{{ __('shadow-theme::shadow.products.no_tags') }}</div>
                            @endif
                        </div>

                        <div class="rounded-xl border border-base-300/70 bg-base-200/35 p-3">
                            <div class="text-xs text-base-content/60">{{ __('shadow-theme::shadow.products.categories') }}</div>
                            @if($product->categories->isEmpty())
                                <div class="mt-1 text-sm text-base-content/70">{{ __('shadow-theme::shadow.products.no_categories_assigned') }}</div>
                            @else
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($product->categories as $category)
                                        <a href="{{ route('shadow.products.index', ['category' => $category->slug, 'scope' => $scope]) }}" class="badge badge-ghost">
                                            {{ $category->name ?? $category->slug }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.6rem] border border-base-300/70 bg-base-100/95 p-5 shadow-md">
                    <div class="mb-2 text-sm font-medium text-base-content/75">{{ __('shadow-theme::shadow.products.developer_access') }}</div>
                    @if($shadowTheme['isAuthenticated'])
                        <p class="text-sm text-base-content/80">{{ __('shadow-theme::shadow.products.authenticated_access_message') }}</p>
                        <a href="{{ route('shadow.apps.index') }}" class="btn btn-outline btn-sm mt-3 rounded-xl">{{ __('shadow-theme::shadow.products.manage_my_apps') }}</a>
                    @else
                        <p class="text-sm text-base-content/80">{{ __('shadow-theme::shadow.products.sign_in_to_manage') }}</p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('shadow.auth.login') }}" class="btn btn-primary btn-sm rounded-xl">{{ __('shadow-theme::shadow.auth.sign_in') }}</a>
                            @if(config('shadow-theme.features.registration', true))
                                <a href="{{ route('shadow.auth.register') }}" class="btn btn-ghost btn-sm rounded-xl">{{ __('shadow-theme::shadow.auth.register') }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div
            x-data="shadowProductDetailsTabs({ specUrl: @js($specViewerUrl) })"
            x-init="init()"
            class="rounded-[1.75rem] border border-base-300/70 bg-base-100/95 shadow-md"
        >
                <div class="border-b border-base-300/70 px-4 pt-4 sm:px-6">
                    <div class="tabs tabs-boxed w-full justify-start gap-2 overflow-x-auto rounded-2xl bg-base-200/70 p-2">
                        <button
                            type="button"
                            class="tab h-auto rounded-xl px-4 py-2 text-sm"
                            x-bind:class="activeTab === 'overview' ? 'tab-active' : ''"
                            x-on:click="setTab('overview')"
                        >
                            {{ __('shadow-theme::shadow.products.tabs.overview') }}
                        </button>
                        <button
                            type="button"
                            class="tab h-auto rounded-xl px-4 py-2 text-sm"
                            x-bind:class="activeTab === 'spec' ? 'tab-active' : ''"
                            x-on:click="setTab('spec')"
                        >
                            {{ __('shadow-theme::shadow.products.tabs.api_spec') }}
                        </button>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div x-show="activeTab === 'overview'" x-cloak class="space-y-6">
                        <div>
                            <h2 class="text-lg font-semibold">{{ __('shadow-theme::shadow.products.tabs.description') }}</h2>
                            @if($description !== '')
                                <div class="prose prose-sm mt-3 max-w-none text-base-content/85 dark:prose-invert">
                                    {!! nl2br(e($description)) !!}
                                </div>
                            @else
                                <div class="mt-3 rounded-2xl border border-dashed border-base-300/80 bg-base-200/20 p-4 text-sm text-base-content/70">
                                    {{ __('shadow-theme::shadow.products.description_unavailable') }}
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-base-300/70 bg-base-200/25 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold">{{ __('shadow-theme::shadow.products.tabs.integration_guide') }}</h3>
                                    <p class="mt-1 text-sm text-base-content/70">{{ __('shadow-theme::shadow.products.integration_guide_help') }}</p>
                                </div>
                                @if($integrationGuideUrl)
                                    <a href="{{ $integrationGuideUrl }}" target="_blank" rel="noreferrer" class="btn btn-outline btn-sm rounded-xl">
                                        {{ __('shadow-theme::shadow.products.open_integration_guide') }}
                                    </a>
                                @endif
                            </div>

                            @if($integrationGuideUrl)
                                <div class="mt-4 overflow-hidden rounded-2xl border border-base-300/70 bg-base-100 shadow-sm">
                                    <iframe
                                        src="{{ $integrationGuideUrl }}"
                                        title="{{ __('shadow-theme::shadow.products.integration_guide_frame_title') }}"
                                        class="h-[28rem] w-full"
                                        loading="lazy"
                                    ></iframe>
                                </div>
                            @else
                                <div class="mt-4 rounded-2xl border border-dashed border-base-300/80 bg-base-100/60 p-4 text-sm text-base-content/70">
                                    {{ __('shadow-theme::shadow.products.integration_guide_unavailable') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div x-show="activeTab === 'spec'" x-cloak class="space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold">{{ __('shadow-theme::shadow.products.tabs.api_spec') }}</h2>
                                <p class="mt-1 text-sm text-base-content/70">{{ __('shadow-theme::shadow.products.api_spec_help') }}</p>
                            </div>
                            @if($specUrl)
                                <a href="{{ $specUrl }}" target="_blank" rel="noreferrer" class="btn btn-outline btn-sm rounded-xl">
                                    {{ __('shadow-theme::shadow.products.open_api_spec') }}
                                </a>
                            @endif
                        </div>

                        @if($specUrl)
                            <div class="overflow-hidden rounded-2xl border border-base-300/70 bg-white shadow-sm">
                                <div id="shadow-swagger-viewer" class="min-h-[42rem]"></div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-base-300/80 bg-base-200/20 p-4 text-sm text-base-content/70">
                                {{ __('shadow-theme::shadow.products.api_spec_unavailable') }}
                            </div>
                        @endif
                    </div>
                </div>
        </div>
    </section>
@endsection

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        #shadow-swagger-viewer {
            background:
                linear-gradient(180deg, color-mix(in srgb, var(--color-base-100) 96%, transparent), color-mix(in srgb, var(--color-base-200) 88%, transparent));
            color: var(--color-base-content);
        }
        #shadow-swagger-viewer .swagger-ui {
            font-family: inherit;
            color: var(--color-base-content);
            background: transparent;
        }
        #shadow-swagger-viewer .swagger-ui,
        #shadow-swagger-viewer .swagger-ui .wrapper,
        #shadow-swagger-viewer .swagger-ui .information-container,
        #shadow-swagger-viewer .swagger-ui .info {
            background: transparent;
        }
        #shadow-swagger-viewer .swagger-ui .wrapper {
            padding-top: 1.5rem;
        }
        #shadow-swagger-viewer .swagger-ui .topbar,
        #shadow-swagger-viewer .swagger-ui .scheme-container {
            display: none;
        }
        #shadow-swagger-viewer .swagger-ui .information-container {
            padding-bottom: 0;
        }
        #shadow-swagger-viewer .swagger-ui .info .title,
        #shadow-swagger-viewer .swagger-ui .info hgroup.main a,
        #shadow-swagger-viewer .swagger-ui .info p,
        #shadow-swagger-viewer .swagger-ui .opblock-tag,
        #shadow-swagger-viewer .swagger-ui .opblock .opblock-summary-description,
        #shadow-swagger-viewer .swagger-ui .opblock .opblock-summary-path,
        #shadow-swagger-viewer .swagger-ui .opblock .opblock-summary-method,
        #shadow-swagger-viewer .swagger-ui .model-title,
        #shadow-swagger-viewer .swagger-ui table thead tr td,
        #shadow-swagger-viewer .swagger-ui table thead tr th,
        #shadow-swagger-viewer .swagger-ui .responses-inner h4,
        #shadow-swagger-viewer .swagger-ui .responses-inner h5,
        #shadow-swagger-viewer .swagger-ui .tab li,
        #shadow-swagger-viewer .swagger-ui section.models h4,
        #shadow-swagger-viewer .swagger-ui section.models h5,
        #shadow-swagger-viewer .swagger-ui label,
        #shadow-swagger-viewer .swagger-ui .parameter__name,
        #shadow-swagger-viewer .swagger-ui .parameter__type,
        #shadow-swagger-viewer .swagger-ui .response-col_status,
        #shadow-swagger-viewer .swagger-ui .response-col_links,
        #shadow-swagger-viewer .swagger-ui .errors-wrapper hgroup h4,
        #shadow-swagger-viewer .swagger-ui .errors-wrapper .errors h4 {
            color: var(--color-base-content);
        }
        #shadow-swagger-viewer .swagger-ui .info .description,
        #shadow-swagger-viewer .swagger-ui .markdown p,
        #shadow-swagger-viewer .swagger-ui .parameter__deprecated,
        #shadow-swagger-viewer .swagger-ui .parameter__in,
        #shadow-swagger-viewer .swagger-ui .response-col_description,
        #shadow-swagger-viewer .swagger-ui .opblock-description-wrapper p,
        #shadow-swagger-viewer .swagger-ui .opblock-external-docs-wrapper p,
        #shadow-swagger-viewer .swagger-ui .opblock-title_normal p,
        #shadow-swagger-viewer .swagger-ui .renderedMarkdown p {
            color: color-mix(in srgb, var(--color-base-content) 78%, transparent);
        }
        #shadow-swagger-viewer .swagger-ui .opblock-tag,
        #shadow-swagger-viewer .swagger-ui .opblock,
        #shadow-swagger-viewer .swagger-ui .model-container,
        #shadow-swagger-viewer .swagger-ui .responses-table,
        #shadow-swagger-viewer .swagger-ui .scheme-container,
        #shadow-swagger-viewer .swagger-ui .dialog-ux .modal-ux,
        #shadow-swagger-viewer .swagger-ui section.models {
            border-color: color-mix(in srgb, var(--color-base-content) 12%, transparent);
        }
        #shadow-swagger-viewer .swagger-ui .opblock,
        #shadow-swagger-viewer .swagger-ui .model-container,
        #shadow-swagger-viewer .swagger-ui section.models,
        #shadow-swagger-viewer .swagger-ui .responses-table,
        #shadow-swagger-viewer .swagger-ui .dialog-ux .modal-ux {
            background: color-mix(in srgb, var(--color-base-100) 86%, var(--color-base-200));
            box-shadow: 0 10px 24px -18px rgb(15 23 42 / 0.32);
        }
        #shadow-swagger-viewer .swagger-ui .opblock .opblock-summary,
        #shadow-swagger-viewer .swagger-ui .tab li,
        #shadow-swagger-viewer .swagger-ui .responses-inner,
        #shadow-swagger-viewer .swagger-ui .model-box {
            border-color: color-mix(in srgb, var(--color-base-content) 10%, transparent);
        }
        #shadow-swagger-viewer .swagger-ui input,
        #shadow-swagger-viewer .swagger-ui textarea,
        #shadow-swagger-viewer .swagger-ui select {
            background: var(--color-base-100);
            color: var(--color-base-content);
            border-color: color-mix(in srgb, var(--color-base-content) 14%, transparent);
        }
        #shadow-swagger-viewer .swagger-ui .btn,
        #shadow-swagger-viewer .swagger-ui .download-contents {
            border-radius: 0.9rem;
            border-color: color-mix(in srgb, var(--color-base-content) 12%, transparent);
            color: var(--color-base-content);
        }
        #shadow-swagger-viewer .swagger-ui .btn.authorize,
        #shadow-swagger-viewer .swagger-ui .download-contents {
            background: color-mix(in srgb, var(--color-primary) 14%, var(--color-base-100));
            color: color-mix(in srgb, var(--color-primary) 78%, var(--color-base-content));
        }
        #shadow-swagger-viewer .swagger-ui .opblock.opblock-get {
            background: linear-gradient(180deg, color-mix(in srgb, #10b981 12%, var(--color-base-100)), color-mix(in srgb, #10b981 7%, var(--color-base-100)));
            border-color: color-mix(in srgb, #10b981 36%, transparent);
        }
        #shadow-swagger-viewer .swagger-ui .opblock.opblock-post {
            background: linear-gradient(180deg, color-mix(in srgb, #3b82f6 12%, var(--color-base-100)), color-mix(in srgb, #3b82f6 7%, var(--color-base-100)));
            border-color: color-mix(in srgb, #3b82f6 36%, transparent);
        }
        #shadow-swagger-viewer .swagger-ui .opblock.opblock-put,
        #shadow-swagger-viewer .swagger-ui .opblock.opblock-patch {
            background: linear-gradient(180deg, color-mix(in srgb, #f59e0b 12%, var(--color-base-100)), color-mix(in srgb, #f59e0b 7%, var(--color-base-100)));
            border-color: color-mix(in srgb, #f59e0b 36%, transparent);
        }
        #shadow-swagger-viewer .swagger-ui .opblock.opblock-delete {
            background: linear-gradient(180deg, color-mix(in srgb, #ef4444 12%, var(--color-base-100)), color-mix(in srgb, #ef4444 7%, var(--color-base-100)));
            border-color: color-mix(in srgb, #ef4444 36%, transparent);
        }
        #shadow-swagger-viewer .swagger-ui .highlight-code,
        #shadow-swagger-viewer .swagger-ui .microlight,
        #shadow-swagger-viewer .swagger-ui pre {
            background: color-mix(in srgb, var(--color-base-300) 55%, var(--color-base-100));
            color: var(--color-base-content);
        }
        [data-theme="night"] #shadow-swagger-viewer .swagger-ui .opblock-tag:hover,
        [data-theme="night"] #shadow-swagger-viewer .swagger-ui .opblock .opblock-summary:hover {
            background: color-mix(in srgb, var(--color-base-200) 78%, transparent);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.shadowProductDetailsTabs = function shadowProductDetailsTabs(config) {
            return {
                activeTab: 'overview',
                specUrl: config?.specUrl || null,
                swaggerLoaded: false,
                init() {
                    if (window.location.hash === '#spec' && this.specUrl) {
                        this.setTab('spec');
                    }
                },
                setTab(tab) {
                    this.activeTab = tab;
                    if (tab === 'spec') {
                        this.$nextTick(() => this.mountSwagger());
                    }
                },
                async mountSwagger() {
                    if (!this.specUrl || this.swaggerLoaded || typeof window.SwaggerUIBundle === 'undefined') {
                        return;
                    }

                    let swaggerConfig = {
                        url: this.specUrl,
                    };

                    try {
                        const response = await window.fetch(this.specUrl, {
                            headers: {
                                Accept: 'application/json, application/yaml, text/yaml, */*',
                            },
                        });

                        const contentType = response.headers.get('content-type') || '';

                        if (response.ok && contentType.includes('json')) {
                            swaggerConfig = {
                                spec: await response.json(),
                            };
                        }
                    } catch (_error) {
                        // Fall back to the URL-based loader when prefetching fails.
                    }

                    window.SwaggerUIBundle({
                        ...swaggerConfig,
                        dom_id: '#shadow-swagger-viewer',
                        deepLinking: true,
                        presets: [
                            window.SwaggerUIBundle.presets.apis,
                        ],
                        layout: 'BaseLayout',
                    });

                    this.swaggerLoaded = true;
                },
            };
        };
    </script>
@endpush
