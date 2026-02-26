@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
])

<div class="form-control w-full">
    @if($label)
        <label class="label" for="{{ $name }}"><span class="label-text">{{ $label }}</span></label>
    @endif
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'input input-bordered w-full']) }}
    />
    @if($hint)
        <label class="label"><span class="label-text-alt text-base-content/60">{{ $hint }}</span></label>
    @endif
</div>
