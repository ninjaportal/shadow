@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.auth.forgot_password'))

@section('content')
<x-shadow::auth.shell>
    <div class="mx-auto w-full max-w-lg lg:mx-0">
        <div class="mb-5 text-center">
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('shadow-theme::shadow.auth.forgot_password') }}</h1>
            <p class="mt-2 text-sm text-base-content/70">{{ __('shadow-theme::shadow.auth.forgot_password_subtitle') }}</p>
        </div>
        <x-shadow::ui.card class="border-base-300/70 bg-base-100/90 shadow-xl lg:shadow-2xl">
            <form method="POST" action="{{ route('shadow.auth.password.email') }}" class="space-y-4">
                @csrf
                <x-shadow::ui.input :label="__('shadow-theme::shadow.common.email')" name="email" type="email" :value="old('email')" required />
                <div class="flex justify-end">
                    <button class="btn btn-primary shadow-lg shadow-primary/20">{{ __('shadow-theme::shadow.auth.send_reset_link') }}</button>
                </div>
            </form>
        </x-shadow::ui.card>
    </div>
</x-shadow::auth.shell>
@endsection
