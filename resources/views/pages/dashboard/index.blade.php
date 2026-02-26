@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.dashboard.title'))

@section('content')
    <x-shadow::ui.page-header :title="__('shadow-theme::shadow.dashboard.heading')" :subtitle="__('shadow-theme::shadow.dashboard.subtitle')" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-shadow::ui.card :title="__('shadow-theme::shadow.dashboard.cards.my_apps')"><div class="text-3xl font-semibold">{{ number_format($appsCount) }}</div></x-shadow::ui.card>
        <x-shadow::ui.card :title="__('shadow-theme::shadow.dashboard.cards.visible_products')"><div class="text-3xl font-semibold">{{ number_format($visibleProductsCount) }}</div></x-shadow::ui.card>
        <x-shadow::ui.card :title="__('shadow-theme::shadow.dashboard.cards.account_status')">
            <div class="flex items-center gap-2">
                <x-shadow::app.status-badge :status="$user->status ?? null" />
            </div>
        </x-shadow::ui.card>
        <x-shadow::ui.card :title="__('shadow-theme::shadow.nav.mfa')">
            @if(is_array($mfaSettings ?? null))
                <div class="text-sm">
                    <div class="font-medium">{{ data_get($mfaSettings, 'profile.is_enabled') ? __('shadow-theme::shadow.common.enabled') : __('shadow-theme::shadow.common.disabled') }}</div>
                    <div class="text-base-content/70">{{ trans_choice('shadow-theme::shadow.mfa.factors_configured_count', (int) data_get($mfaSettings, 'effective.enabled_factor_count', 0), ['count' => (int) data_get($mfaSettings, 'effective.enabled_factor_count', 0)]) }}</div>
                </div>
            @else
                <div class="text-sm text-base-content/70">{{ __('shadow-theme::shadow.common.not_available') }}</div>
            @endif
        </x-shadow::ui.card>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <x-shadow::ui.card :title="__('shadow-theme::shadow.dashboard.quick_actions')">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('shadow.apps.index') }}" class="btn btn-primary btn-sm">{{ __('shadow-theme::shadow.dashboard.actions.manage_apps') }}</a>
                <a href="{{ route('shadow.products.index', ['scope' => 'mine']) }}" class="btn btn-outline btn-sm">{{ __('shadow-theme::shadow.dashboard.actions.browse_products') }}</a>
                <a href="{{ route('shadow.profile.edit') }}" class="btn btn-ghost btn-sm">{{ __('shadow-theme::shadow.dashboard.actions.edit_profile') }}</a>
                @if($shadowTheme['mfa']->available() && $shadowTheme['mfa']->enabledForConsumer())
                    <a href="{{ route('shadow.mfa.settings') }}" class="btn btn-ghost btn-sm">{{ __('shadow-theme::shadow.dashboard.actions.mfa_settings') }}</a>
                @endif
            </div>
        </x-shadow::ui.card>

        <x-shadow::ui.card :title="__('shadow-theme::shadow.dashboard.recent_apps')" :subtitle="__('shadow-theme::shadow.dashboard.recent_apps_subtitle')">
            @if($recentApps->isEmpty())
                <p class="text-sm text-base-content/70">{{ __('shadow-theme::shadow.dashboard.no_apps_yet') }}</p>
            @else
                <div class="space-y-2">
                    @foreach($recentApps as $app)
                        @php
                            $name = method_exists($app, 'getName') ? $app->getName() : ($app->name ?? __('shadow-theme::shadow.apps.single'));
                            $status = method_exists($app, 'getStatus') ? $app->getStatus() : ($app->status ?? null);
                        @endphp
                        <div class="flex items-center justify-between rounded-xl border border-base-300 p-3">
                            <a href="{{ route('shadow.apps.show', ['appName' => $name]) }}" class="font-medium hover:underline">{{ $name }}</a>
                            <x-shadow::app.status-badge :status="$status" />
                        </div>
                    @endforeach
                </div>
            @endif
        </x-shadow::ui.card>
    </div>
@endsection
