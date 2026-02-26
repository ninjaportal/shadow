@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.apps.manage_title', ['name' => $appData['name'] ?? __('shadow-theme::shadow.apps.single')]))

@section('content')
    <div class="mb-4">
        <a href="{{ route('shadow.apps.index') }}" class="link link-hover text-sm">
            {{ ($shadowTheme['isRtl'] ?? false) ? '→' : '←' }} {{ __('shadow-theme::shadow.apps.back_to_apps') }}
        </a>
    </div>

    <x-shadow::ui.page-header :title="__('shadow-theme::shadow.apps.manage_title', ['name' => $appData['display_name'] ?? $appData['name'] ?? __('shadow-theme::shadow.apps.single')])" :subtitle="__('shadow-theme::shadow.apps.manage_subtitle')">
        <x-slot:actions>
            <x-shadow::app.status-badge :status="$appData['status']" />
            <form method="POST" action="{{ route('shadow.apps.approve', ['appName' => $appData['name']]) }}">@csrf <button class="btn btn-success btn-sm">{{ __('shadow-theme::shadow.common.approve') }}</button></form>
            <form method="POST" action="{{ route('shadow.apps.revoke', ['appName' => $appData['name']]) }}">@csrf <button class="btn btn-warning btn-sm">{{ __('shadow-theme::shadow.common.revoke') }}</button></form>
            <form method="POST" action="{{ route('shadow.apps.destroy', ['appName' => $appData['name']]) }}" onsubmit="return confirm(@json(__('shadow-theme::shadow.confirm.delete_app')));">@csrf @method('DELETE') <button class="btn btn-error btn-sm">{{ __('shadow-theme::shadow.common.delete') }}</button></form>
        </x-slot:actions>
    </x-shadow::ui.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <x-shadow::ui.card :title="__('shadow-theme::shadow.apps.app_settings')">
            <form method="POST" action="{{ route('shadow.apps.update', ['appName' => $appData['name']]) }}" class="grid gap-4">
                @csrf
                @method('PUT')
                <x-shadow::ui.input :label="__('shadow-theme::shadow.apps.app_name')" name="name" :value="$appData['name']" required />
                <x-shadow::ui.input :label="__('shadow-theme::shadow.apps.callback_url')" name="callbackUrl" type="url" :value="$appData['callback_url']" />
                <div class="flex justify-end">
                    <button class="btn btn-primary">{{ __('shadow-theme::shadow.apps.update_action') }}</button>
                </div>
            </form>
        </x-shadow::ui.card>

        <x-shadow::ui.card :title="__('shadow-theme::shadow.apps.create_credential')" :subtitle="__('shadow-theme::shadow.apps.create_credential_subtitle')">
            @unless($canCreateApps)
                <div class="alert alert-warning mb-3"><span>{{ __('shadow-theme::shadow.apps.pending_approval_credential_warning') }}</span></div>
            @endunless
            <form method="POST" action="{{ route('shadow.apps.credentials.store', ['appName' => $appData['name']]) }}" class="grid gap-4">
                @csrf
                <x-shadow::ui.select :label="__('shadow-theme::shadow.nav.api_products')" name="apiProducts" :options="$productOptions" :selected="old('apiProducts', [])" :multiple="true" class="min-h-36" />
                <x-shadow::ui.input :label="__('shadow-theme::shadow.apps.expires_in_ms')" name="expiresIn" type="number" :value="old('expiresIn', -1)" :hint="__('shadow-theme::shadow.apps.expires_hint')" />
                <div class="flex justify-end">
                    <button class="btn btn-primary" @disabled(!$canCreateApps)>{{ __('shadow-theme::shadow.apps.create_credential_action') }}</button>
                </div>
            </form>
        </x-shadow::ui.card>
    </div>

    <div class="mt-6">
        <x-shadow::ui.card :title="__('shadow-theme::shadow.apps.credentials_title')" :subtitle="__('shadow-theme::shadow.apps.credentials_subtitle')">
            @if(empty($appData['credentials']))
                <p class="text-sm text-base-content/70">{{ __('shadow-theme::shadow.apps.no_credentials_yet') }}</p>
            @else
                <div class="space-y-4">
                    @foreach($appData['credentials'] as $credential)
                        <div class="rounded-2xl border border-base-300 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="font-mono text-sm font-semibold">{{ $credential['key'] ?? __('shadow-theme::shadow.apps.unknown_key') }}</div>
                                    <div class="mt-1 text-xs text-base-content/65">{{ __('shadow-theme::shadow.apps.secret_label') }} {{ $credential['secret_short'] ?? __('shadow-theme::shadow.apps.hidden_unavailable') }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <x-shadow::app.status-badge :status="$credential['status']" />
                                        @if(!empty($credential['issued_at']))<span class="badge badge-ghost">{{ __('shadow-theme::shadow.apps.issued_at', ['value' => $credential['issued_at']]) }}</span>@endif
                                        @if(!empty($credential['expires_at']))<span class="badge badge-ghost">{{ __('shadow-theme::shadow.apps.expires_at', ['value' => $credential['expires_at']]) }}</span>@endif
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('shadow.apps.credentials.approve', ['appName' => $appData['name'], 'key' => $credential['key']]) }}">@csrf <button class="btn btn-success btn-xs">{{ __('shadow-theme::shadow.common.approve') }}</button></form>
                                    <form method="POST" action="{{ route('shadow.apps.credentials.revoke', ['appName' => $appData['name'], 'key' => $credential['key']]) }}">@csrf <button class="btn btn-warning btn-xs">{{ __('shadow-theme::shadow.common.revoke') }}</button></form>
                                    <form method="POST" action="{{ route('shadow.apps.credentials.destroy', ['appName' => $appData['name'], 'key' => $credential['key']]) }}" onsubmit="return confirm(@json(__('shadow-theme::shadow.confirm.delete_credential')));">@csrf @method('DELETE') <button class="btn btn-error btn-xs">{{ __('shadow-theme::shadow.common.delete') }}</button></form>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
                                <div>
                                    <div class="mb-2 text-sm font-medium">{{ __('shadow-theme::shadow.apps.credential_products') }}</div>
                                    @if(empty($credential['api_products']))
                                        <p class="text-sm text-base-content/70">{{ __('shadow-theme::shadow.apps.no_products_attached') }}</p>
                                    @else
                                        <div class="space-y-2">
                                            @foreach($credential['api_products'] as $product)
                                                <div class="flex flex-col gap-2 rounded-xl border border-base-300 p-3 sm:flex-row sm:items-center sm:justify-between">
                                                        <div>
                                                        <div class="font-medium text-sm">{{ $product['name'] ?? __('shadow-theme::shadow.products.single') }}</div>
                                                        @if(!empty($product['status']))
                                                            @php
                                                                $productStatusValue = strtolower((string) $product['status']);
                                                                $productStatusKey = 'shadow-theme::shadow.statuses.'.$productStatusValue;
                                                                $productStatusLabel = trans()->has($productStatusKey) ? __($productStatusKey) : (string) $product['status'];
                                                            @endphp
                                                            <div class="text-xs text-base-content/65">{{ __('shadow-theme::shadow.common.status_colon', ['status' => $productStatusLabel]) }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="flex flex-wrap gap-2">
                                                        <form method="POST" action="{{ route('shadow.apps.credentials.products.approve', ['appName' => $appData['name'], 'key' => $credential['key'], 'product' => $product['name']]) }}">@csrf <button class="btn btn-xs btn-success">{{ __('shadow-theme::shadow.common.approve') }}</button></form>
                                                        <form method="POST" action="{{ route('shadow.apps.credentials.products.revoke', ['appName' => $appData['name'], 'key' => $credential['key'], 'product' => $product['name']]) }}">@csrf <button class="btn btn-xs btn-warning">{{ __('shadow-theme::shadow.common.revoke') }}</button></form>
                                                        <form method="POST" action="{{ route('shadow.apps.credentials.products.remove', ['appName' => $appData['name'], 'key' => $credential['key'], 'product' => $product['name']]) }}" onsubmit="return confirm(@json(__('shadow-theme::shadow.confirm.remove_credential_product')));">@csrf @method('DELETE') <button class="btn btn-xs btn-ghost text-error">{{ __('shadow-theme::shadow.common.remove') }}</button></form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <div class="mb-2 text-sm font-medium">{{ __('shadow-theme::shadow.apps.add_products') }}</div>
                                    <form method="POST" action="{{ route('shadow.apps.credentials.products.add', ['appName' => $appData['name'], 'key' => $credential['key']]) }}" class="space-y-3">
                                        @csrf
                                        <x-shadow::ui.select name="apiProducts" :options="$productOptions" :selected="[]" :multiple="true" class="min-h-28" />
                                        <button class="btn btn-primary btn-sm w-full">{{ __('shadow-theme::shadow.apps.add_to_credential') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-shadow::ui.card>
    </div>
@endsection
