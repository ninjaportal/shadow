@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.apps.my_apps'))

@section('content')
    <x-shadow::ui.page-header :title="__('shadow-theme::shadow.apps.my_apps')" :subtitle="__('shadow-theme::shadow.apps.index_subtitle')">
        <x-slot:actions>
            <a href="{{ route('shadow.apps.create') }}" class="btn btn-primary btn-sm">{{ __('shadow-theme::shadow.apps.create_action') }}</a>
        </x-slot:actions>
    </x-shadow::ui.page-header>

    @if(!empty($appsError))
        <div class="alert alert-error mb-4"><span>{{ $appsError }}</span></div>
    @endif

    @unless($canCreateApps)
        <div class="alert alert-warning mb-4"><span>{{ __('shadow-theme::shadow.apps.pending_approval_warning') }}</span></div>
    @endunless

    @if($apps->isEmpty())
        <x-shadow::ui.empty-state :title="__('shadow-theme::shadow.apps.empty_title')" :message="__('shadow-theme::shadow.apps.empty_message')">
            <a href="{{ route('shadow.apps.create') }}" class="btn btn-primary btn-sm">{{ __('shadow-theme::shadow.apps.create_action') }}</a>
        </x-shadow::ui.empty-state>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach($apps as $app)
                <x-shadow::ui.card>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $app['display_name'] ?? $app['name'] }}</h3>
                            <p class="text-sm text-base-content/70">{{ $app['name'] }}</p>
                        </div>
                        <x-shadow::app.status-badge :status="$app['status']" />
                    </div>
                    <div class="mt-4 text-sm text-base-content/70">
                        <div>{{ trans_choice('shadow-theme::shadow.apps.credentials_count', count($app['credentials'] ?? []), ['count' => count($app['credentials'] ?? [])]) }}</div>
                        <div>{{ trans_choice('shadow-theme::shadow.apps.linked_products_count', count($app['api_products'] ?? []), ['count' => count($app['api_products'] ?? [])]) }}</div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('shadow.apps.show', ['appName' => $app['name']]) }}" class="btn btn-primary btn-sm">{{ __('shadow-theme::shadow.common.manage') }}</a>
                        <form method="POST" action="{{ route('shadow.apps.destroy', ['appName' => $app['name']]) }}" onsubmit="return confirm(@json(__('shadow-theme::shadow.confirm.delete_app')));">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-ghost btn-sm text-error">{{ __('shadow-theme::shadow.common.delete') }}</button>
                        </form>
                    </div>
                </x-shadow::ui.card>
            @endforeach
        </div>
    @endif
@endsection
