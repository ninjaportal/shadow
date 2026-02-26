@props(['product', 'scope' => 'public'])

@php
    $name = (string) ($product->name ?? $product->slug ?? __('shadow-theme::shadow.products.single'));
    $shortDescription = (string) ($product->short_description ?? '');
    $description = (string) ($product->description ?? '');
    $visibility = (string) ($product->visibility ?? 'public');
    $visibilityKey = 'shadow-theme::shadow.products.visibility.'.strtolower($visibility);
    $visibilityLabel = trans()->has($visibilityKey) ? __($visibilityKey) : ucfirst($visibility);
@endphp

<x-shadow::ui.card class="h-full">
    <div class="flex h-full flex-col gap-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold leading-tight">{{ $name }}</h3>
                <p class="mt-1 text-xs uppercase tracking-wide text-base-content/60">{{ $product->apigee_product_id ?? $product->slug }}</p>
            </div>
            <span class="badge {{ $visibility === 'private' ? 'badge-warning' : 'badge-success' }} badge-outline">{{ $visibilityLabel }}</span>
        </div>

        <p class="text-sm text-base-content/75 line-clamp-3">{{ $shortDescription !== '' ? $shortDescription : \Illuminate\Support\Str::limit(strip_tags($description), 140) }}</p>

        @if(method_exists($product, 'categories') && $product->relationLoaded('categories') && $product->categories->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach($product->categories->take(4) as $category)
                    <span class="badge badge-ghost">{{ $category->name ?? $category->slug }}</span>
                @endforeach
            </div>
        @endif

        <div class="mt-auto flex items-center justify-between pt-2">
            <a href="{{ route('shadow.products.show', ['slug' => $product->slug, 'scope' => $scope]) }}" class="btn btn-sm btn-primary">{{ __('shadow-theme::shadow.products.view_details') }}</a>
            @if(!empty($product->swagger_url))
                <a href="{{ $product->swagger_url }}" class="link link-hover text-sm" target="_blank" rel="noreferrer">{{ __('shadow-theme::shadow.products.spec_short') }}</a>
            @endif
        </div>
    </div>
</x-shadow::ui.card>
