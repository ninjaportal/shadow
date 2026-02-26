@extends('shadow-theme::layouts.app')

@section('title', ($shadowTheme['branding']['name'] ?? config('app.name')).' '.__('shadow-theme::shadow.home.title_suffix'))

@section('content')
    <section class="shadow-hero-bg relative -mt-20 [margin-inline:calc(50%-50vw)] overflow-hidden border-t border-white/10 bg-slate-950 py-20">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-0 opacity-35 [background-image:linear-gradient(to_right,rgba(255,255,255,0.08)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:44px_44px]"></div>
            <div class="absolute -left-14 top-8 size-56 rounded-full bg-primary/25 blur-3xl"></div>
            <div class="absolute right-0 top-12 size-64 rounded-full bg-info/30 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-52 w-72 rounded-full bg-accent/20 blur-3xl"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-950/30 to-slate-900/70"></div>
        </div>

        <div class="relative mx-auto w-full max-w-7xl px-4 pb-12 pt-28 sm:px-6 sm:pb-14 sm:pt-32 lg:px-8 lg:pb-20 lg:pt-36">
            <div dir="ltr" class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-12">
                <div class="relative order-2 lg:order-1">
                    <div class="absolute -left-3 -top-3 hidden h-28 w-28 rounded-3xl bg-primary/20 blur-2xl lg:block"></div>
                    <div class="relative rounded-3xl border border-white/15 bg-white/5 p-4 shadow-2xl shadow-black/40 backdrop-blur-xl">
                        <div class="mb-3 flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full bg-error"></span>
                                <span class="size-2.5 rounded-full bg-warning"></span>
                                <span class="size-2.5 rounded-full bg-success"></span>
                            </div>
                            <div class="text-xs font-medium text-white/60">curl</div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-[#0b1220]/95 p-4 text-[#cbd5e1] shadow-inner shadow-black/35" dir="ltr">
                            <pre class="max-w-full whitespace-pre-wrap break-words text-xs leading-6 sm:text-sm"><code><span class="text-slate-300">$</span> <span class="text-slate-100">curl</span> <span class="text-cyan-300">https://api.example.com/apps</span> \
  <span class="text-violet-300">--json</span> <span class="text-emerald-300">'{"name":"weather-console"}'</span>

{
  <span class="text-sky-300">"status"</span>: <span class="text-emerald-300">"created"</span>,
  <span class="text-sky-300">"app"</span>: <span class="text-emerald-300">"weather-console"</span>,
  <span class="text-sky-300">"key"</span>: <span class="text-amber-200">"••••••••"</span>
}</code></pre>
                        </div>
                    </div>
                </div>

                <div dir="{{ $shadowTheme['direction'] }}" class="order-1 lg:order-2">
                    <h1 class="max-w-xl text-2xl font-semibold leading-tight tracking-tight text-white sm:text-4xl lg:text-[2.6rem]">
                        {{ __('shadow-theme::shadow.home.hero_title') }}
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-7 text-white/75 sm:text-base">
                        {{ __('shadow-theme::shadow.home.hero_subtitle') }}
                    </p>

                    <div class="mt-6">
                        <a href="{{ route('shadow.products.index') }}" class="btn btn-primary border-0 shadow-xl shadow-primary/35">
                            {{ __('shadow-theme::shadow.home.explore_products') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="pointer-events-none absolute inset-x-0 bottom-0 text-base-100">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none" class="block h-10 w-full sm:h-14 lg:h-20">
                <path fill="currentColor" d="M0,64 C180,118 320,16 540,46 C760,76 880,132 1080,102 C1240,78 1336,34 1440,62 L1440,120 L0,120 Z"></path>
            </svg>
        </div>
    </section>

    <section class="mt-10">
        <x-shadow::ui.page-header :title="__('shadow-theme::shadow.home.featured.title')" :subtitle="__('shadow-theme::shadow.home.featured.subtitle')" />

        @if($featuredProducts->isEmpty())
            <x-shadow::ui.empty-state :title="__('shadow-theme::shadow.home.featured.empty_title')" :message="__('shadow-theme::shadow.home.featured.empty_message')" />
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($featuredProducts as $product)
                    <x-shadow::product.card :product="$product" :scope="$shadowTheme['isAuthenticated'] ? 'mine' : 'public'" />
                @endforeach
            </div>
            <div class="mt-6">
                <a href="{{ route('shadow.products.index', ['scope' => $shadowTheme['isAuthenticated'] ? 'mine' : 'public']) }}" class="btn btn-outline">{{ __('shadow-theme::shadow.home.featured.view_full_catalog') }}</a>
            </div>
        @endif
    </section>

    <section class="mt-10 grid gap-6 lg:grid-cols-2">
        <x-shadow::ui.card :title="__('shadow-theme::shadow.home.onboarding.title')" :subtitle="__('shadow-theme::shadow.home.onboarding.subtitle')">
            <p class="text-sm text-base-content/80">
                {{ __('shadow-theme::shadow.home.onboarding.description') }}
            </p>
        </x-shadow::ui.card>

        <x-shadow::ui.card :title="__('shadow-theme::shadow.home.categories.title')" :subtitle="__('shadow-theme::shadow.home.categories.subtitle')">
            @if($categories->isEmpty())
                <p class="text-sm text-base-content/70">{{ __('shadow-theme::shadow.home.categories.empty') }}</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($categories as $category)
                        <a href="{{ route('shadow.products.index', ['category' => $category->slug, 'scope' => $shadowTheme['isAuthenticated'] ? 'mine' : 'public']) }}" class="badge badge-ghost px-3 py-3">
                            {{ $category->name ?? $category->slug }}
                        </a>
                    @endforeach
                </div>
            @endif
        </x-shadow::ui.card>
    </section>
@endsection
