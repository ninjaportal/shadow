@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.apps.create_title'))

@section('content')
    <x-shadow::ui.page-header :title="__('shadow-theme::shadow.apps.create_title')" :subtitle="__('shadow-theme::shadow.apps.create_subtitle')" />

    @unless($canCreateApps)
        <div class="alert alert-warning mb-4"><span>{{ __('shadow-theme::shadow.apps.pending_approval_create_warning') }}</span></div>
    @endunless

    <x-shadow::ui.card>
        <form method="POST" action="{{ route('shadow.apps.store') }}" class="grid gap-4 lg:grid-cols-2">
            @csrf
            <x-shadow::ui.input :label="__('shadow-theme::shadow.apps.app_name')" name="name" :placeholder="__('shadow-theme::shadow.apps.app_name_placeholder')" required />
            <x-shadow::ui.input :label="__('shadow-theme::shadow.apps.callback_url')" name="callbackUrl" type="url" :placeholder="__('shadow-theme::shadow.apps.callback_url_placeholder')" />
            <x-shadow::ui.select :label="__('shadow-theme::shadow.apps.initial_api_products')" name="apiProducts" :options="$productOptions" :selected="old('apiProducts', [])" :multiple="true" class="lg:col-span-2 min-h-40" />
            <div class="lg:col-span-2 flex items-center justify-between gap-3">
                <a href="{{ route('shadow.apps.index') }}" class="btn btn-ghost">{{ __('shadow-theme::shadow.common.back') }}</a>
                <button class="btn btn-primary" @disabled(!$canCreateApps)>{{ __('shadow-theme::shadow.apps.create_action') }}</button>
            </div>
        </form>
    </x-shadow::ui.card>
@endsection
