@extends('shadow-theme::layouts.app')

@section('title', __('shadow-theme::shadow.products.title'))

@section('content')
    <x-shadow::ui.page-header :title="__('shadow-theme::shadow.products.title')" :subtitle="__('shadow-theme::shadow.products.subtitle')" />

    <x-shadow::ui.card class="mb-6">
        <form method="GET" action="{{ route('shadow.products.index') }}" class="grid gap-4 md:grid-cols-4 md:items-end">
            <x-shadow::ui.input :label="__('shadow-theme::shadow.products.search')" name="q" :value="request('q')" :placeholder="__('shadow-theme::shadow.products.search_placeholder')" />

            <x-shadow::ui.select
                :label="__('shadow-theme::shadow.products.category')"
                name="category"
                :selected="request('category')"
                :options="$categories->map(fn($category) => ['value' => $category->slug, 'label' => (string) ($category->name ?? $category->slug)])->values()->all()"
                :placeholder="__('shadow-theme::shadow.products.all_categories')"
            />

            <x-shadow::ui.select
                :label="__('shadow-theme::shadow.products.visibility_scope')"
                name="scope"
                :selected="$scope"
                :options="collect([['value' => 'public', 'label' => __('shadow-theme::shadow.products.scope_public')]])->merge($shadowTheme['isAuthenticated'] ? [['value' => 'mine', 'label' => __('shadow-theme::shadow.products.scope_mine')]] : [])->all()"
            />

            <div class="flex gap-2">
                <button class="btn btn-primary flex-1">{{ __('shadow-theme::shadow.common.filter') }}</button>
                <a href="{{ route('shadow.products.index') }}" class="btn btn-ghost">{{ __('shadow-theme::shadow.common.reset') }}</a>
            </div>
        </form>
    </x-shadow::ui.card>

    @if($products->isEmpty())
        <x-shadow::ui.empty-state :title="__('shadow-theme::shadow.products.empty_title')" :message="__('shadow-theme::shadow.products.empty_message')" />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($products as $product)
                <x-shadow::product.card :product="$product" :scope="$scope" />
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
@endsection
