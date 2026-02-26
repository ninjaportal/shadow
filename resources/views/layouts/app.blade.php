<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $shadowTheme['locale'] ?? app()->getLocale()) }}" dir="{{ $shadowTheme['direction'] ?? 'ltr' }}" x-data="shadowPortalTheme()" x-bind:data-theme="mode" class="h-full">
<head>
    @php
        $shadowThemeLocale = strtolower(str_replace('_', '-', $shadowTheme['locale'] ?? app()->getLocale()));
        $shadowThemeArabicLocale = $shadowThemeLocale === 'ar' || str_starts_with($shadowThemeLocale, 'ar-');
        $shadowThemeAuthFlowPage = request()->routeIs('shadow.auth.*');
        $shadowThemeHomePage = request()->routeIs('shadow.home');
        $shadowHeaderUser = $shadowTheme['user'] ?? null;
        $shadowHeaderName = trim((string) ($shadowHeaderUser->full_name ?? ''));
        if ($shadowHeaderName === '') {
            $shadowHeaderName = trim((string) (($shadowHeaderUser->first_name ?? '').' '.($shadowHeaderUser->last_name ?? '')));
        }
        if ($shadowHeaderName === '') {
            $shadowHeaderName = (string) ($shadowHeaderUser->email ?? '');
        }
        $shadowHeaderAvatarSeed = trim((string) (($shadowHeaderUser->first_name ?? '').' '.($shadowHeaderUser->last_name ?? '')));
        if ($shadowHeaderAvatarSeed === '') {
            $shadowHeaderAvatarSeed = (string) ($shadowHeaderUser->email ?? 'U');
        }
        $shadowHeaderAvatarWords = preg_split('/\s+/u', trim($shadowHeaderAvatarSeed)) ?: [];
        $shadowHeaderInitials = collect($shadowHeaderAvatarWords)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
        if ($shadowHeaderInitials === '') {
            $shadowHeaderInitials = 'U';
        }
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', ($shadowTheme['branding']['name'] ?? config('app.name')).' '.__('shadow-theme::shadow.layout.portal_suffix'))</title>
    @if($shadowThemeArabicLocale)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --shadow-accent: {{ $shadowTheme['palette']['accent_color'] ?? '#22d3ee' }};
            --shadow-accent-2: {{ $shadowTheme['palette']['accent_color_2'] ?? '#38bdf8' }};
            --shadow-hero-glow: {{ $shadowTheme['palette']['hero_glow'] ?? 'rgba(34, 211, 238, 0.25)' }};
        }
        @if($shadowThemeArabicLocale)
        body {
            font-family: "Tajawal", ui-sans-serif, system-ui, sans-serif;
        }
        @endif
        .shadow-hero-bg {
            background-image:
                radial-gradient(circle at 15% 15%, var(--shadow-hero-glow), transparent 45%),
                radial-gradient(circle at 85% 20%, color-mix(in srgb, var(--shadow-accent-2) 22%, transparent), transparent 45%),
                linear-gradient(180deg, color-mix(in srgb, var(--shadow-accent) 6%, transparent), transparent 35%);
        }
        .shadow-header-on-hero .navbar-start > .btn-ghost,
        .shadow-header-on-hero .navbar-end > .btn-ghost,
        .shadow-header-on-hero .navbar-end > a.btn-ghost,
        .shadow-header-on-hero .navbar-end > .dropdown > .btn-ghost {
            color: rgba(255, 255, 255, 0.92);
        }
        .shadow-header-on-hero .navbar-start > .btn-ghost:hover,
        .shadow-header-on-hero .navbar-end > .btn-ghost:hover,
        .shadow-header-on-hero .navbar-end > a.btn-ghost:hover,
        .shadow-header-on-hero .navbar-end > .dropdown > .btn-ghost:hover {
            background-color: rgba(255, 255, 255, 0.08);
        }
        .shadow-header-on-hero .navbar-start .menu-horizontal > li > a {
            color: rgba(255, 255, 255, 0.84);
        }
        .shadow-header-on-hero .navbar-start .menu-horizontal > li > a:hover,
        .shadow-header-on-hero .navbar-start .menu-horizontal > li > a.active {
            background-color: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .shadow-header-on-hero .navbar-end .text-sm {
            color: rgba(255, 255, 255, 0.8);
        }
        .shadow-header-on-hero .shadow-user-menu-trigger {
            color: rgba(255, 255, 255, 0.95);
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .shadow-header-on-hero .shadow-user-menu-trigger:hover {
            background-color: rgba(255, 255, 255, 0.10);
        }
        .shadow-user-menu-trigger .shadow-avatar-chip {
            background: linear-gradient(135deg, color-mix(in srgb, var(--shadow-accent-2) 75%, white), color-mix(in srgb, var(--shadow-accent) 70%, white));
            color: #06111f;
        }
        .shadow-main-navbar .menu-horizontal > li > a,
        .shadow-main-navbar .dropdown-content .menu > li > a {
            transition:
                background-color 180ms ease,
                color 180ms ease,
                box-shadow 180ms ease;
        }
        .shadow-main-navbar .menu-horizontal > li > a:active,
        .shadow-main-navbar .dropdown-content .menu > li > a:active {
            transform: none;
        }
        .shadow-user-menu-trigger {
            border-color: color-mix(in srgb, currentColor 12%, transparent);
            background-color: color-mix(in srgb, currentColor 2%, transparent);
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.06);
        }
        .shadow-user-menu-trigger:hover {
            border-color: color-mix(in srgb, currentColor 16%, transparent);
            background-color: color-mix(in srgb, currentColor 5%, transparent);
        }
        .shadow-user-menu-panel {
            border-color: color-mix(in srgb, var(--fallback-bc,oklch(var(--bc)/1)) 12%, transparent);
            box-shadow: 0 20px 40px -16px rgb(15 23 42 / 0.22);
        }
        .shadow-user-menu-panel .menu {
            width: 100%;
        }
        .shadow-user-menu-panel .menu > li {
            width: 100%;
        }
        .shadow-user-menu-panel .menu > li > a {
            width: 100%;
            justify-content: flex-start;
            border-radius: 0.9rem;
            padding-block: 0.7rem;
            padding-inline: 0.85rem;
        }
        .shadow-user-menu-panel .menu > li > a.active {
            background-color: color-mix(in srgb, var(--shadow-accent) 10%, transparent);
            color: color-mix(in srgb, var(--shadow-accent-2) 75%, currentColor);
        }
        .shadow-user-menu-panel .menu > li > a:hover {
            background-color: color-mix(in srgb, currentColor 4%, transparent);
        }
    </style>
    @stack('head')
</head>
<body class="min-h-full overflow-x-clip bg-base-100 text-base-content">
<div class="min-h-screen flex flex-col">
    <header
        class="sticky top-0 z-40 transition-colors duration-300"
        x-bind:class="headerOverHero() ? 'shadow-header-on-hero border-b border-transparent bg-transparent' : 'border-b border-base-300/60 bg-base-100/90 backdrop-blur'"
    >
        <div class="shadow-main-navbar navbar mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="navbar-start gap-2 lg:gap-4">
                <a href="{{ route('shadow.home') }}" class="btn btn-ghost px-2 text-lg font-semibold tracking-tight">
                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-base-content text-base-100">{{ strtoupper(substr((string) ($shadowTheme['branding']['logo_text'] ?? 'S'), 0, 1)) }}</span>
                    <span>{{ $shadowTheme['branding']['name'] ?? config('app.name') }}</span>
                </a>
                <div class="hidden lg:flex">
                    <ul class="menu menu-horizontal gap-1 px-1">
                        <li><a href="{{ route('shadow.products.index') }}" class="{{ request()->routeIs('shadow.products.*') ? 'active' : '' }}">{{ __('shadow-theme::shadow.nav.api_products') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="navbar-end ms-auto gap-2">
                <button
                    type="button"
                    class="btn btn-ghost btn-sm gap-2 rounded-xl"
                    x-on:click="toggleMode()"
                    x-bind:title="modeLabel()"
                    x-bind:aria-label="modeLabel()"
                >
                    <svg x-show="isDarkMode()" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21M3 12h2.25M18.75 12H21M5.636 5.636l1.591 1.591M16.773 16.773l1.591 1.591M5.636 18.364l1.591-1.591M16.773 7.227l1.591-1.591M15.75 12A3.75 3.75 0 1 1 8.25 12a3.75 3.75 0 0 1 7.5 0Z"/></svg>
                    <svg x-show="!isDarkMode()" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3c-.03.25-.04.5-.04.75a9 9 0 0 0 9.08 9.04c.25 0 .5-.01.75-.04Z"/></svg>
                    <span class="hidden sm:inline" x-text="modeLabel()"></span>
                </button>
                @if($shadowTheme['isAuthenticated'])
                    <div class="dropdown {{ ($shadowTheme['isRtl'] ?? false) ? 'dropdown-left' : 'dropdown-end' }}">
                        <button type="button" class="btn btn-sm gap-2 rounded-2xl border border-base-300/70 bg-base-100/75 px-1.5 pe-3 shadow-user-menu-trigger" tabindex="0">
                            <span class="shadow-avatar-chip inline-flex size-8 items-center justify-center rounded-xl text-xs font-bold shadow-sm">{{ $shadowHeaderInitials }}</span>
                            <span class="hidden max-w-36 truncate text-sm sm:inline">{{ $shadowHeaderName }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 opacity-70" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                        </button>
                        <div tabindex="0" class="shadow-user-menu-panel dropdown-content z-[70] mt-3 w-72 rounded-3xl border border-base-300/60 bg-base-100/95 p-2 shadow-2xl backdrop-blur">
                            <div class="mb-2 flex items-center gap-3 rounded-xl border border-base-300/70 bg-base-200/50 p-3">
                                <span class="shadow-avatar-chip inline-flex size-10 items-center justify-center rounded-xl text-sm font-bold">{{ $shadowHeaderInitials }}</span>
                                <div class="min-w-0">
                                    <div class="truncate font-medium">{{ $shadowHeaderName }}</div>
                                    <div class="truncate text-xs text-base-content/60">{{ $shadowHeaderUser->email ?? '' }}</div>
                                </div>
                            </div>

                            <ul class="menu gap-1 p-0 w-full">
                                <li><a href="{{ route('shadow.dashboard') }}" class="{{ request()->routeIs('shadow.dashboard') ? 'active' : '' }}">{{ __('shadow-theme::shadow.nav.dashboard') }}</a></li>
                                <li><a href="{{ route('shadow.apps.index') }}" class="{{ request()->routeIs('shadow.apps.*') ? 'active' : '' }}">{{ __('shadow-theme::shadow.nav.apps') }}</a></li>
                                <li><a href="{{ route('shadow.profile.edit') }}" class="{{ request()->routeIs('shadow.profile.*') ? 'active' : '' }}">{{ __('shadow-theme::shadow.nav.profile') }}</a></li>
                                @if($shadowTheme['mfa']->available() && $shadowTheme['mfa']->enabledForConsumer())
                                    <li><a href="{{ route('shadow.mfa.settings') }}" class="{{ request()->routeIs('shadow.mfa.*') ? 'active' : '' }}">{{ __('shadow-theme::shadow.nav.mfa') }}</a></li>
                                @endif
                            </ul>

                            <div class="my-2 border-t border-base-300/70"></div>

                            <form method="POST" action="{{ route('shadow.auth.logout') }}">
                                @csrf
                                <button class="btn btn-ghost btn-sm w-full justify-start text-error hover:bg-error/10">{{ __('shadow-theme::shadow.auth.sign_out') }}</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('shadow.auth.login') }}" class="btn btn-ghost btn-sm">{{ __('shadow-theme::shadow.auth.sign_in') }}</a>
                    @if(config('shadow-theme.features.registration', true))
                        <a href="{{ route('shadow.auth.register') }}" class="btn btn-primary btn-sm">{{ __('shadow-theme::shadow.nav.get_started') }}</a>
                    @endif
                @endif
                <div class="dropdown {{ ($shadowTheme['isRtl'] ?? false) ? 'dropdown-left' : 'dropdown-end' }} lg:hidden">
                    <button class="btn btn-ghost btn-square" tabindex="0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <ul tabindex="0" class="menu dropdown-content z-[60] mt-3 w-64 rounded-box border border-base-300 bg-base-100 p-2 shadow-xl">
                        <li><a href="{{ route('shadow.products.index') }}">{{ __('shadow-theme::shadow.nav.api_products') }}</a></li>
                        @if(! $shadowTheme['isAuthenticated'])
                            <li><a href="{{ route('shadow.auth.login') }}">{{ __('shadow-theme::shadow.auth.sign_in') }}</a></li>
                            @if(config('shadow-theme.features.registration', true))
                                <li><a href="{{ route('shadow.auth.register') }}">{{ __('shadow-theme::shadow.auth.register') }}</a></li>
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div @class([
            'mx-auto w-full',
            'max-w-7xl px-4 py-6 sm:px-6 lg:px-8' => ! $shadowThemeAuthFlowPage && ! $shadowThemeHomePage,
            'max-w-7xl px-4 pt-0 pb-6 sm:px-6 lg:px-8' => $shadowThemeHomePage && ! $shadowThemeAuthFlowPage,
            'px-0 py-0' => $shadowThemeAuthFlowPage,
        ])>
            <x-shadow::ui.flash />
            @yield('content')
        </div>
    </main>

    @unless($shadowThemeAuthFlowPage)
    <footer class="border-t border-base-300/60 bg-base-200/40">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-3 px-4 py-6 text-sm text-base-content/70 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div>
                <div class="font-medium text-base-content">{{ $shadowTheme['branding']['name'] ?? config('app.name') }}</div>
                <div>{{ $shadowTheme['branding']['tagline'] ?? '' }}</div>
            </div>
            @if(!empty($shadowTheme['branding']['support_email']))
                <a class="link link-hover" href="mailto:{{ $shadowTheme['branding']['support_email'] }}">{{ $shadowTheme['branding']['support_email'] }}</a>
            @endif
        </div>
    </footer>
    @endunless
</div>

<script>
    window.shadowPortalTheme = function shadowPortalTheme() {
        const darkTheme = @json($shadowTheme['palette']['dark'] ?? 'night');
        const lightTheme = @json($shadowTheme['palette']['light'] ?? 'corporate');
        const defaultMode = @json($shadowTheme['palette']['default_mode'] ?? 'dark');
        const labels = {
            light: @json(__('shadow-theme::shadow.theme.light_mode')),
            dark: @json(__('shadow-theme::shadow.theme.dark_mode')),
        };
        const homeHeroPage = @json($shadowThemeHomePage);

        return {
            darkTheme,
            lightTheme,
            labels,
            homeHeroPage,
            scrolled: false,
            mode: defaultMode === 'light' ? lightTheme : darkTheme,
            init() {
                const saved = window.localStorage.getItem('shadow-theme-mode');
                if (saved === 'light') this.mode = this.lightTheme;
                if (saved === 'dark') this.mode = this.darkTheme;
                this.applyMode();
                this.handleScroll();
                window.addEventListener('scroll', () => this.handleScroll(), { passive: true });
            },
            toggleMode() {
                const next = this.mode === this.darkTheme ? 'light' : 'dark';
                this.mode = next === 'dark' ? this.darkTheme : this.lightTheme;
                window.localStorage.setItem('shadow-theme-mode', next);
                this.applyMode();
            },
            applyMode() {
                document.documentElement.setAttribute('data-theme', this.mode);
            },
            isDarkMode() {
                return this.mode === this.darkTheme;
            },
            handleScroll() {
                this.scrolled = window.scrollY > 8;
            },
            headerOverHero() {
                return this.homeHeroPage && !this.scrolled;
            },
            modeLabel() {
                return this.mode === this.darkTheme ? this.labels.light : this.labels.dark;
            }
        }
    };
</script>
@stack('scripts')
</body>
</html>
