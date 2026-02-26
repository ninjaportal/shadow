@props(['label' => null, 'name' => null, 'value' => null, 'rows' => 4, 'placeholder' => null])

<div class="form-control w-full">
    @if($label)
        <label class="label" for="{{ $name }}"><span class="label-text">{{ $label }}</span></label>
    @endif
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'textarea textarea-bordered w-full']) }}
    >{{ old($name, $value) }}</textarea>
</div>
