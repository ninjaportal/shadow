@props([
    'imageUrl' => (string) config('shadow-theme.auth.ui.side_image_url', ''),
    'imagePosition' => 'center',
])

<div class="grid min-h-[calc(100svh-4.5rem)] w-full gap-0 lg:grid-cols-[1.2fr_0.8fr]" dir="ltr">
    <section class="hidden lg:block lg:min-h-0">
        <div class="relative h-full overflow-hidden border-y border-s border-base-300/70 shadow-2xl shadow-base-content/15 lg:rounded-none">
            @if($imageUrl !== '')
                <div
                    class="absolute inset-0 bg-base-200 bg-cover"
                    style="background-image: url('{{ $imageUrl }}'); background-position: {{ $imagePosition }};"
                ></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-base-300 via-base-200 to-base-100"></div>
            @endif

            <div class="absolute inset-0 bg-gradient-to-br from-neutral/75 via-neutral/35 to-primary/15"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(255,255,255,0.14),transparent_40%),radial-gradient(circle_at_85%_20%,rgba(59,130,246,0.20),transparent_45%),radial-gradient(circle_at_50%_85%,rgba(34,197,94,0.12),transparent_50%)]"></div>

            <div class="absolute inset-x-8 bottom-8 h-16 rounded-2xl border border-white/20 bg-white/10 shadow-lg backdrop-blur-sm"></div>
        </div>
    </section>

    <section class="min-w-0 bg-base-100/80 px-4 py-8 sm:px-6 lg:flex lg:items-center lg:px-10 lg:py-10" dir="{{ $shadowTheme['direction'] ?? 'ltr' }}">
        <div class="mx-auto w-full max-w-md lg:mx-0">
            {{ $slot }}
        </div>
    </section>
</div>
