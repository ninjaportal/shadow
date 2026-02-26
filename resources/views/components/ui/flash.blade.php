@php
    $success = session('shadow.success');
    $error = session('shadow.error');
@endphp

@if($success)
    <div class="alert alert-success mb-4">
        <span>{{ $success }}</span>
    </div>
@endif

@if($error)
    <div class="alert alert-error mb-4">
        <span>{{ $error }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error mb-4 items-start">
        <div>
            <div class="font-semibold">{{ __('shadow-theme::shadow.flash.fix_following') }}</div>
            <ul class="mt-1 list-disc ps-5 text-sm">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
