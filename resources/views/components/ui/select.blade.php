@props(['label' => null, 'name' => null, 'options' => [], 'selected' => null, 'multiple' => false, 'placeholder' => null])
@php
    $selectedValues = collect((array) old($name, $selected))->map(fn ($v) => (string) $v)->all();
@endphp
<div class="form-control w-full">
    @if($label)
        <label class="label" for="{{ $name }}"><span class="label-text">{{ $label }}</span></label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}" {{ $multiple ? 'multiple' : '' }} {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}>
        @if(!$multiple && $placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $option)
            @php
                $value = is_array($option) ? (string) ($option['value'] ?? '') : (string) $option;
                $labelText = is_array($option) ? (string) ($option['label'] ?? $value) : (string) $option;
            @endphp
            <option value="{{ $value }}" @selected(in_array($value, $selectedValues, true))>{{ $labelText }}</option>
        @endforeach
    </select>
</div>
