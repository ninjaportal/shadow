@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.mfa.settings_title'))

@section('content')
    <x-shadow::ui.page-header :title="__('shadow-theme::shadow.mfa.settings_title')" :subtitle="__('shadow-theme::shadow.mfa.settings_subtitle')" />

    @php
        $profile = (array) data_get($mfaSettings, 'profile', []);
        $factors = collect((array) data_get($mfaSettings, 'factors', []));
        $availableDrivers = collect((array) data_get($mfaSettings, 'available_drivers', []));
        $mfaEnabledOnAccount = (bool) ($profile['is_enabled'] ?? false);
        $mfaEffectiveRequired = (bool) ($profile['effective_required'] ?? false);
        $mfaEnabledFactorCount = (int) data_get($mfaSettings, 'effective.enabled_factor_count', 0);
        $authenticatorSetup = session('shadow.mfa.authenticator_setup');
        $authenticatorOtpAuthUri = is_array($authenticatorSetup) ? (string) data_get($authenticatorSetup, 'setup.otpauth_uri', '') : '';
        $authenticatorQrCode = is_array($authenticatorSetup)
            ? app(\NinjaPortal\Shadow\Support\QrCodeRenderer::class)->renderSvgDataUri($authenticatorOtpAuthUri)
            : null;
        $emailOtpChallengeToken = $emailOtpEnrollmentChallengeToken ?? session('shadow_theme.mfa_email_otp_enrollment.challenge_token');
        $availableDriverLabels = $availableDrivers
            ->map(function ($driver) {
                $key = 'shadow-theme::shadow.mfa.driver_labels.'.$driver;

                return trans()->has($key)
                    ? __($key)
                    : ucfirst(str_replace('_', ' ', (string) $driver));
            })
            ->filter()
            ->values();
    @endphp

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-6">
            <x-shadow::ui.card
                :title="__('shadow-theme::shadow.mfa.overview_title')"
                :subtitle="__('shadow-theme::shadow.mfa.overview_subtitle')"
                class="border-base-300/70 bg-base-100/95 shadow-md"
            >
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-base-300/70 bg-base-200/40 p-3">
                        <div class="text-xs text-base-content/60">{{ __('shadow-theme::shadow.mfa.current_status') }}</div>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="badge {{ $mfaEnabledOnAccount ? 'badge-success' : 'badge-ghost' }}">
                                {{ $mfaEnabledOnAccount ? __('shadow-theme::shadow.common.enabled') : __('shadow-theme::shadow.common.disabled') }}
                            </span>
                            @if($mfaEffectiveRequired)
                                <span class="badge badge-warning">{{ __('shadow-theme::shadow.mfa.effective_required') }} {{ __('shadow-theme::shadow.common.yes') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-xl border border-base-300/70 bg-base-200/40 p-3">
                        <div class="text-xs text-base-content/60">{{ __('shadow-theme::shadow.mfa.enabled_factor_count') }}</div>
                        <div class="mt-2 text-2xl font-semibold tracking-tight">{{ $mfaEnabledFactorCount }}</div>
                        <div class="text-xs text-base-content/65">
                            {{ trans_choice('shadow-theme::shadow.mfa.factors_configured_count', $mfaEnabledFactorCount, ['count' => $mfaEnabledFactorCount]) }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-base-300/70 bg-base-200/30 p-3">
                    <div class="mb-2 text-xs text-base-content/60">{{ __('shadow-theme::shadow.mfa.available_drivers') }}</div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($availableDriverLabels as $driverLabel)
                            <span class="badge badge-outline">{{ $driverLabel }}</span>
                        @empty
                            <span class="text-sm text-base-content/70">—</span>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-base-300/70 bg-base-200/35 p-3 text-sm">
                    <div class="mb-1 font-medium">{{ __('shadow-theme::shadow.mfa.quick_tip_title') }}</div>
                    <p class="text-base-content/75">{{ __('shadow-theme::shadow.mfa.quick_tip_body') }}</p>
                </div>
            </x-shadow::ui.card>

            <x-shadow::ui.card
                :title="__('shadow-theme::shadow.mfa.general_settings')"
                class="border-base-300/70 bg-base-100/95 shadow-md"
            >
                <form method="POST" action="{{ route('shadow.mfa.settings.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <label class="fieldset-label flex items-center gap-3 rounded-xl border border-base-300/70 bg-base-200/30 p-3">
                        <input type="hidden" name="is_enabled" value="0">
                        <input type="checkbox" class="toggle toggle-primary" name="is_enabled" value="1" @checked($mfaEnabledOnAccount)>
                        <span>{{ __('shadow-theme::shadow.mfa.enable_for_account') }}</span>
                    </label>

                    <x-shadow::ui.select
                        :label="__('shadow-theme::shadow.mfa.preferred_driver')"
                        name="preferred_driver"
                        :selected="$profile['preferred_driver'] ?? null"
                        :options="$availableDrivers->map(function ($driver) { $key = 'shadow-theme::shadow.mfa.driver_labels.'.$driver; return ['value' => $driver, 'label' => trans()->has($key) ? __($key) : ucfirst(str_replace('_', ' ', $driver))]; })->all()"
                        :placeholder="__('shadow-theme::shadow.mfa.auto_select')"
                    />

                    <div class="rounded-xl border border-base-300/70 bg-base-200/35 p-3 text-sm">
                        <div><span class="font-medium">{{ __('shadow-theme::shadow.mfa.effective_required') }}</span> {{ $mfaEffectiveRequired ? __('shadow-theme::shadow.common.yes') : __('shadow-theme::shadow.common.no') }}</div>
                        <div class="mt-1"><span class="font-medium">{{ __('shadow-theme::shadow.mfa.configured_factors') }}</span> {{ $mfaEnabledFactorCount }}</div>
                    </div>

                    <div class="flex justify-end">
                        <button class="btn btn-primary">{{ __('shadow-theme::shadow.mfa.save_settings') }}</button>
                    </div>
                </form>
            </x-shadow::ui.card>
        </div>

        <div class="space-y-6">
            <x-shadow::ui.card
                :title="__('shadow-theme::shadow.mfa.configured_factors_title')"
                class="border-base-300/70 bg-base-100/95 shadow-md"
            >
                @if($factors->isEmpty())
                    <div class="rounded-xl border border-dashed border-base-300/80 bg-base-200/20 p-4 text-sm text-base-content/70">
                        {{ __('shadow-theme::shadow.mfa.no_factors_configured') }}
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($factors as $factor)
                            <div class="flex flex-col gap-3 rounded-xl border border-base-300/70 bg-base-200/20 p-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    @php
                                        $factorDriver = (string) data_get($factor, 'driver');
                                        $factorDriverKey = 'shadow-theme::shadow.mfa.driver_labels.'.$factorDriver;
                                    @endphp
                                    <div class="font-medium">{{ trans()->has($factorDriverKey) ? __($factorDriverKey) : ucfirst(str_replace('_', ' ', $factorDriver)) }}</div>
                                    @if(filled(data_get($factor, 'label')))
                                        <div class="text-xs text-base-content/65">{{ data_get($factor, 'label') }}</div>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    @if(data_get($factor, 'is_primary')) <span class="badge badge-primary">{{ __('shadow-theme::shadow.mfa.primary') }}</span> @endif
                                    @if(data_get($factor, 'is_enabled')) <span class="badge badge-success">{{ __('shadow-theme::shadow.common.enabled') }}</span> @else <span class="badge badge-ghost">{{ __('shadow-theme::shadow.common.disabled') }}</span> @endif
                                    @if(data_get($factor, 'is_verified')) <span class="badge badge-info">{{ __('shadow-theme::shadow.common.verified') }}</span> @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-shadow::ui.card>

            <x-shadow::ui.card
                :title="__('shadow-theme::shadow.mfa.setup_methods_title')"
                class="border-base-300/70 bg-base-100/95 shadow-md"
            >
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-base-300/70 bg-base-200/15 p-4">
                        <div class="mb-3">
                            <h3 class="text-base font-semibold">{{ __('shadow-theme::shadow.mfa.authenticator_app') }}</h3>
                            <p class="mt-1 text-sm text-base-content/70">{{ __('shadow-theme::shadow.mfa.authenticator_description') }}</p>
                        </div>

                        <form method="POST" action="{{ route('shadow.mfa.authenticator.start') }}" class="space-y-3">
                            @csrf
                            <x-shadow::ui.input :label="__('shadow-theme::shadow.mfa.label_optional')" name="label" :placeholder="__('shadow-theme::shadow.mfa.device_label_placeholder')" />
                            <button class="btn btn-primary btn-sm w-full">{{ __('shadow-theme::shadow.mfa.generate_setup') }}</button>
                        </form>

                        @if(is_array($authenticatorSetup))
                            <div class="mt-4 rounded-xl border border-base-300/70 bg-base-100/80 p-3 text-sm">
                                <div class="grid gap-3 sm:grid-cols-[auto_1fr] sm:items-start">
                                    <div class="mx-auto w-full max-w-[11rem] sm:mx-0">
                                        @if(is_string($authenticatorQrCode) && $authenticatorQrCode !== '')
                                            <div class="rounded-xl border border-base-300/70 bg-base-100 p-2 shadow-sm">
                                                <img
                                                    src="{{ $authenticatorQrCode }}"
                                                    alt="{{ __('shadow-theme::shadow.mfa.authenticator_qr_alt') }}"
                                                    class="mx-auto block size-40 rounded-lg bg-white p-2"
                                                    loading="lazy"
                                                >
                                            </div>
                                            <p class="mt-2 text-center text-xs text-base-content/70">
                                                {{ __('shadow-theme::shadow.mfa.scan_qr_code_hint') }}
                                            </p>
                                        @else
                                            <div class="rounded-xl border border-dashed border-base-300/80 bg-base-100/70 p-3 text-xs text-base-content/70">
                                                {{ __('shadow-theme::shadow.mfa.qr_code_unavailable_hint') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="font-medium">{{ __('shadow-theme::shadow.mfa.setup_secret') }}</div>
                                        <div class="mt-1 font-mono break-all text-xs">{{ data_get($authenticatorSetup, 'setup.secret') }}</div>
                                        <div class="mt-2 text-xs text-base-content/70">{{ __('shadow-theme::shadow.mfa.or_enter_secret_manually') }}</div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="font-medium">{{ __('shadow-theme::shadow.mfa.setup_uri') }}</div>
                                    <div class="mt-1 break-all text-xs text-base-content/70">{{ $authenticatorOtpAuthUri }}</div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('shadow.mfa.authenticator.confirm') }}" class="mt-4 space-y-3">
                            @csrf
                            <x-shadow::ui.input :label="__('shadow-theme::shadow.mfa.verification_code')" name="code" :placeholder="__('shadow-theme::shadow.mfa.code_placeholder')" />
                            <button class="btn btn-success btn-sm w-full">{{ __('shadow-theme::shadow.mfa.confirm_authenticator') }}</button>
                        </form>

                        <form method="POST" action="{{ route('shadow.mfa.authenticator.disable') }}" class="mt-3" onsubmit="return confirm(@json(__('shadow-theme::shadow.confirm.disable_authenticator_mfa')));">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-ghost btn-sm w-full text-error hover:bg-error/10">{{ __('shadow-theme::shadow.mfa.disable_authenticator') }}</button>
                        </form>
                    </div>

                    <div class="rounded-2xl border border-base-300/70 bg-base-200/15 p-4">
                        <div class="mb-3">
                            <h3 class="text-base font-semibold">{{ __('shadow-theme::shadow.mfa.email_otp') }}</h3>
                            <p class="mt-1 text-sm text-base-content/70">{{ __('shadow-theme::shadow.mfa.email_otp_description') }}</p>
                        </div>

                        <form method="POST" action="{{ route('shadow.mfa.email-otp.start') }}" class="space-y-3">
                            @csrf
                            <button class="btn btn-primary btn-sm w-full">{{ __('shadow-theme::shadow.mfa.send_email_verification_code') }}</button>
                        </form>

                        <form method="POST" action="{{ route('shadow.mfa.email-otp.confirm') }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="challenge_token" value="{{ $emailOtpChallengeToken }}">
                            <x-shadow::ui.input :label="__('shadow-theme::shadow.mfa.email_otp_code')" name="code" :placeholder="__('shadow-theme::shadow.mfa.code_placeholder')" />
                            <button class="btn btn-success btn-sm w-full">{{ __('shadow-theme::shadow.mfa.confirm_email_otp') }}</button>
                        </form>

                        <form method="POST" action="{{ route('shadow.mfa.email-otp.disable') }}" class="mt-3" onsubmit="return confirm(@json(__('shadow-theme::shadow.confirm.disable_email_otp_mfa')));">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-ghost btn-sm w-full text-error hover:bg-error/10">{{ __('shadow-theme::shadow.mfa.disable_email_otp') }}</button>
                        </form>
                    </div>
                </div>
            </x-shadow::ui.card>
        </div>
    </div>
@endsection
