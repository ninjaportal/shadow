@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.auth.create_account'))

@section('content')
<x-shadow::auth.shell>
    <div class="mx-auto w-full max-w-xl lg:mx-0">
        <div class="mb-5 text-center">
            <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('shadow-theme::shadow.auth.create_account_heading') }}</h1>
            <p class="mt-2 text-sm text-base-content/70 sm:text-base">{{ __('shadow-theme::shadow.auth.register_subtitle') }}</p>
        </div>

        <x-shadow::ui.card class="border-base-300/70 bg-base-100/90 shadow-xl lg:shadow-2xl">
            <form method="POST" action="{{ route('shadow.auth.register.store') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <x-shadow::ui.input :label="__('shadow-theme::shadow.common.first_name')" name="first_name" required />
                <x-shadow::ui.input :label="__('shadow-theme::shadow.common.last_name')" name="last_name" />
                <x-shadow::ui.input :label="__('shadow-theme::shadow.common.email')" name="email" type="email" class="sm:col-span-2" required />
                <x-shadow::ui.input :label="__('shadow-theme::shadow.common.password')" name="password" type="password" required />
                <x-shadow::ui.input :label="__('shadow-theme::shadow.auth.confirm_password')" name="password_confirmation" type="password" required />
                <div class="sm:col-span-2 flex items-center justify-between gap-3 pt-2">
                    <a href="{{ route('shadow.auth.login') }}" class="link link-hover text-sm">{{ __('shadow-theme::shadow.auth.already_have_account') }}</a>
                    <button class="btn btn-primary shadow-lg shadow-primary/20">{{ __('shadow-theme::shadow.auth.create_account') }}</button>
                </div>
            </form>
        </x-shadow::ui.card>
    </div>
</x-shadow::auth.shell>
@endsection
