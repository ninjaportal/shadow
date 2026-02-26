@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.mfa.challenge_title'))

@section('content')
@php
    $challenge = is_array($challenge ?? null) ? $challenge : [];
@endphp
<x-shadow::auth.shell>
    <div class="mx-auto w-full max-w-lg lg:mx-0">
        <div class="mb-5 text-center">
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('shadow-theme::shadow.mfa.challenge_heading') }}</h1>
            <p class="mt-2 text-sm text-base-content/70">{{ __('shadow-theme::shadow.mfa.challenge_subtitle') }}</p>
        </div>

        <x-shadow::ui.card class="border-base-300/70 bg-base-100/90 shadow-xl lg:shadow-2xl">
            <div class="mb-4 rounded-2xl border border-base-300/70 bg-base-200/40 p-4 text-sm">
                @php
                    $driver = (string) ($challenge['driver'] ?? 'mfa');
                    $driverKey = 'shadow-theme::shadow.mfa.driver_labels.'.$driver;
                    $driverLabel = trans()->has($driverKey) ? __($driverKey) : ucfirst(str_replace('_', ' ', $driver));
                @endphp
                <div><span class="font-medium">{{ __('shadow-theme::shadow.mfa.method') }}</span> {{ $driverLabel }}</div>
                @if(!empty($challenge['masked_destination']))
                    <div class="mt-1"><span class="font-medium">{{ __('shadow-theme::shadow.mfa.destination') }}</span> {{ $challenge['masked_destination'] }}</div>
                @endif
                @if(!empty($challenge['prompt']))
                    <div class="mt-2 text-base-content/70">{{ $challenge['prompt'] }}</div>
                @endif
            </div>

            <form method="POST" action="{{ route('shadow.auth.mfa.verify') }}" class="space-y-4">
                @csrf
                <x-shadow::ui.input :label="__('shadow-theme::shadow.mfa.verification_code')" name="code" :placeholder="__('shadow-theme::shadow.mfa.code_placeholder')" required autocomplete="one-time-code" />
                <button class="btn btn-primary w-full shadow-lg shadow-primary/20">{{ __('shadow-theme::shadow.mfa.verify_and_sign_in') }}</button>
            </form>

            @if(!empty($challenge['can_resend']))
                <form method="POST" action="{{ route('shadow.auth.mfa.resend') }}" class="mt-3 text-center">
                    @csrf
                    <button class="btn btn-ghost btn-sm">{{ __('shadow-theme::shadow.mfa.resend_code') }}</button>
                </form>
            @endif

            <div class="mt-4 text-center text-sm">
                <a href="{{ route('shadow.auth.login') }}" class="link link-hover">{{ __('shadow-theme::shadow.auth.back_to_sign_in') }}</a>
            </div>
        </x-shadow::ui.card>
    </div>
</x-shadow::auth.shell>
@endsection
