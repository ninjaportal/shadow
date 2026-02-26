@extends('shadow-theme::layouts.app')

@section('title', (string) ($product->name ?? $product->slug ?? __('shadow-theme::shadow.products.single')))

@section('content')
    <div class="mb-4">
        <a href="{{ route('shadow.products.index', ['scope' => $scope]) }}" class="link link-hover text-sm">
            {{ ($shadowTheme['isRtl'] ?? false) ? '→' : '←' }} {{ __('shadow-theme::shadow.products.back_to_products') }}
        </a>
    </div>

    <section class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
        <x-shadow::ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    @php
                        $visibilityValue = strtolower((string) ($product->visibility ?? 'public'));
                        $visibilityKey = 'shadow-theme::shadow.products.visibility.'.$visibilityValue;
                        $visibilityLabel = trans()->has($visibilityKey) ? __($visibilityKey) : ucfirst($visibilityValue);
                    @endphp
                    <div class="badge badge-outline mb-3">{{ $visibilityLabel }}</div>
                    <h1 class="text-2xl font-semibold sm:text-3xl">{{ $product->name ?? $product->slug }}</h1>
                    <p class="mt-2 text-sm text-base-content/70">{{ $product->apigee_product_id ?? $product->slug }}</p>
                </div>
                @if(!empty($product->swagger_url))
                    <a href="{{ $product->swagger_url }}" target="_blank" rel="noreferrer" class="btn btn-primary btn-sm">{{ __('shadow-theme::shadow.products.open_api_spec') }}</a>
                @endif
            </div>

            @if(!empty($product->short_description))
                <p class="mt-6 text-lg text-base-content/85">{{ $product->short_description }}</p>
            @endif

            @if(!empty($product->description))
                <div class="prose prose-sm mt-6 max-w-none text-base-content/85 dark:prose-invert">
                    {!! nl2br(e((string) $product->description)) !!}
                </div>
            @endif
        </x-shadow::ui.card>

        <div class="space-y-6">
            <x-shadow::ui.card :title="__('shadow-theme::shadow.products.categories')">
                @if($product->categories->isEmpty())
                    <p class="text-sm text-base-content/70">{{ __('shadow-theme::shadow.products.no_categories_assigned') }}</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($product->categories as $category)
                            <a href="{{ route('shadow.products.index', ['category' => $category->slug, 'scope' => $scope]) }}" class="badge badge-ghost">
                                {{ $category->name ?? $category->slug }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-shadow::ui.card>

            <x-shadow::ui.card :title="__('shadow-theme::shadow.products.developer_access')">
                @if($shadowTheme['isAuthenticated'])
                    <p class="text-sm text-base-content/80">{{ __('shadow-theme::shadow.products.authenticated_access_message') }}</p>
                    <a href="{{ route('shadow.apps.index') }}" class="btn btn-outline btn-sm mt-3">{{ __('shadow-theme::shadow.products.manage_my_apps') }}</a>
                @else
                    <p class="text-sm text-base-content/80">{{ __('shadow-theme::shadow.products.sign_in_to_manage') }}</p>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('shadow.auth.login') }}" class="btn btn-primary btn-sm">{{ __('shadow-theme::shadow.auth.sign_in') }}</a>
                        @if(config('shadow-theme.features.registration', true))
                            <a href="{{ route('shadow.auth.register') }}" class="btn btn-ghost btn-sm">{{ __('shadow-theme::shadow.auth.register') }}</a>
                        @endif
                    </div>
                @endif
            </x-shadow::ui.card>
        </div>
    </section>
@endsection
