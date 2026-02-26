@props(['title' => null, 'subtitle' => null, 'class' => ''])

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow-sm '.$class]) }}>
    <div class="card-body">
        @if($title || $subtitle)
            <div class="mb-2">
                @if($title)
                    <h3 class="card-title text-base">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-sm text-base-content/70">{{ $subtitle }}</p>
                @endif
            </div>
        @endif
        {{ $slot }}
    </div>
</div>
