@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.profile.title'))

@section('content')
    @php
        $profileName = trim((string) (($user->full_name ?? '') ?: (($user->first_name ?? '').' '.($user->last_name ?? ''))));
        if ($profileName === '') {
            $profileName = (string) ($user->email ?? 'User');
        }
        $profileInitials = collect(preg_split('/\s+/u', $profileName) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
        if ($profileInitials === '') {
            $profileInitials = 'U';
        }
        $status = strtolower((string) ($user->status ?? 'unknown'));
        $statusKey = 'shadow-theme::shadow.statuses.'.$status;
        $statusLabel = trans()->has($statusKey) ? __($statusKey) : (string) ($user->status ?? __('shadow-theme::shadow.common.unknown'));
    @endphp

    <x-shadow::ui.page-header :title="__('shadow-theme::shadow.profile.title')" :subtitle="__('shadow-theme::shadow.profile.subtitle')" />

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-6">
            <x-shadow::ui.card :title="__('shadow-theme::shadow.profile.overview_title')" :subtitle="__('shadow-theme::shadow.profile.overview_subtitle')" class="border-base-300/70 bg-base-100/95 shadow-md">
                <div class="flex items-start gap-4">
                    <div class="inline-flex size-14 items-center justify-center rounded-2xl bg-primary/15 text-primary text-lg font-bold">
                        {{ $profileInitials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-lg font-semibold">{{ $profileName }}</div>
                        <div class="truncate text-sm text-base-content/65">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-base-300/70 bg-base-200/40 p-3">
                        <div class="text-xs text-base-content/60">{{ __('shadow-theme::shadow.profile.account_status') }}</div>
                        <div class="mt-1">
                            <span class="badge badge-outline">{{ $statusLabel }}</span>
                        </div>
                    </div>
                    <div class="rounded-xl border border-base-300/70 bg-base-200/40 p-3">
                        <div class="text-xs text-base-content/60">{{ __('shadow-theme::shadow.profile.read_only_email') }}</div>
                        <div class="mt-1 truncate text-sm font-medium">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-base-300/70 bg-base-200/30 p-3 text-sm">
                    <div class="mb-1 font-medium">{{ __('shadow-theme::shadow.profile.security_note_title') }}</div>
                    <p class="text-base-content/75">{{ __('shadow-theme::shadow.profile.security_note_body') }}</p>
                    @if($shadowTheme['mfa']->available() && $shadowTheme['mfa']->enabledForConsumer())
                        <a href="{{ route('shadow.mfa.settings') }}" class="btn btn-outline btn-sm mt-3">
                            {{ __('shadow-theme::shadow.profile.mfa_settings_cta') }}
                        </a>
                    @endif
                </div>
            </x-shadow::ui.card>
        </div>

        <div class="space-y-6">
            <x-shadow::ui.card :title="__('shadow-theme::shadow.profile.details_title')" class="border-base-300/70 bg-base-100/95 shadow-md">
                <form method="POST" action="{{ route('shadow.profile.update') }}" class="grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <x-shadow::ui.input :label="__('shadow-theme::shadow.common.first_name')" name="first_name" :value="$user->first_name" required />
                    <x-shadow::ui.input :label="__('shadow-theme::shadow.common.last_name')" name="last_name" :value="$user->last_name" />

                    <div class="sm:col-span-2 rounded-xl border border-base-300/70 bg-base-200/35 p-3">
                        <div class="text-xs text-base-content/60">{{ __('shadow-theme::shadow.profile.read_only_email') }}</div>
                        <div class="mt-1 text-sm font-medium">{{ $user->email }}</div>
                    </div>

                    <div class="sm:col-span-2 flex justify-end">
                        <button class="btn btn-primary">{{ __('shadow-theme::shadow.profile.save_profile') }}</button>
                    </div>
                </form>
            </x-shadow::ui.card>

            <x-shadow::ui.card :title="__('shadow-theme::shadow.profile.change_password_title')" class="border-base-300/70 bg-base-100/95 shadow-md">
                <p class="mb-4 text-sm text-base-content/70">{{ __('shadow-theme::shadow.profile.password_hint') }}</p>
                <form method="POST" action="{{ route('shadow.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <x-shadow::ui.input :label="__('shadow-theme::shadow.profile.current_password')" name="current_password" type="password" required />
                    <x-shadow::ui.input :label="__('shadow-theme::shadow.profile.new_password')" name="password" type="password" required />
                    <x-shadow::ui.input :label="__('shadow-theme::shadow.profile.confirm_new_password')" name="password_confirmation" type="password" required />
                    <div class="flex justify-end">
                        <button class="btn btn-primary">{{ __('shadow-theme::shadow.profile.update_password') }}</button>
                    </div>
                </form>
            </x-shadow::ui.card>
        </div>
    </div>
@endsection
