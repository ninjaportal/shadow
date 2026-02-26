@props(['title' => null, 'message' => null])
<div class="rounded-2xl border border-dashed border-base-300 bg-base-200/30 p-8 text-center">
    <h3 class="text-lg font-semibold">{{ $title ?? __('shadow-theme::shadow.common.empty_default_title') }}</h3>
    @if($message)
        <p class="mt-2 text-sm text-base-content/70">{{ $message }}</p>
    @endif
    @if(trim((string) $slot) !== '')
        <div class="mt-4 flex justify-center">{{ $slot }}</div>
    @endif
</div>
