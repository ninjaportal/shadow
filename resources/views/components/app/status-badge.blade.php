@props(['status' => null])
@php
    $status = strtolower(trim((string) $status));
    $class = match ($status) {
        'approved', 'active' => 'badge-success',
        'revoked', 'inactive' => 'badge-error',
        'pending' => 'badge-warning',
        default => 'badge-ghost',
    };
    $statusKey = 'shadow-theme::shadow.statuses.'.$status;
    $label = $status !== '' && trans()->has($statusKey)
        ? __($statusKey)
        : ($status !== '' ? ucfirst($status) : __('shadow-theme::shadow.common.unknown'));
@endphp
<span {{ $attributes->merge(['class' => 'badge '.$class]) }}>{{ $label }}</span>
