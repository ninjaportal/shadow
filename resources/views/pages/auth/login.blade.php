@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.auth.sign_in'))

@section('content')
<x-shadow::auth.shell>
    <div class="mx-auto w-full max-w-md lg:mx-0">
        <x-shadow::ui.card class="border-base-300/70 bg-base-100/90 shadow-xl lg:shadow-2xl">
            <div class="mb-4 text-center">
                <div class="mx-auto mb-3 inline-flex size-12 items-center justify-center rounded-2xl bg-base-content text-base-100 text-lg font-semibold">
                    {{ strtoupper(substr((string) ($shadowTheme['branding']['logo_text'] ?? 'S'), 0, 1)) }}
                </div>
                <h1 class="text-xl font-semibold tracking-tight">{{ __('shadow-theme::shadow.auth.welcome_back') }}</h1>
                <p class="mt-1 text-sm text-base-content/70">
                    {{ __('shadow-theme::shadow.auth.login_subtitle') }}
                </p>
            </div>

            @if(config('shadow-theme.features.registration', true))
                <div class="mb-4 text-center text-sm text-base-content/70">
                    {{ __('shadow-theme::shadow.auth.new_here') }}
                    <a href="{{ route('shadow.auth.register') }}" class="link link-primary">{{ __('shadow-theme::shadow.auth.create_account') }}</a>
                </div>
            @endif

        <form method="POST" action="{{ route('shadow.auth.login.store') }}" class="space-y-4">
            @csrf
            <x-shadow::ui.input :label="__('shadow-theme::shadow.common.email')" name="email" type="email" :value="old('email')" autocomplete="username" required />
            <x-shadow::ui.input :label="__('shadow-theme::shadow.common.password')" name="password" type="password" autocomplete="current-password" required />
            <button class="btn btn-primary w-full shadow-lg shadow-primary/20">{{ __('shadow-theme::shadow.auth.sign_in') }}</button>
        </form>

        @if(config('shadow-theme.features.password_reset', true))
            <div class="mt-4 text-center text-sm">
                <a href="{{ route('shadow.auth.password.request') }}" class="link link-hover">{{ __('shadow-theme::shadow.auth.forgot_password_link') }}</a>
            </div>
        @endif
        </x-shadow::ui.card>
    </div>
</x-shadow::auth.shell>
@endsection
