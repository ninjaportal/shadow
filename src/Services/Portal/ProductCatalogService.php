<?php

namespace NinjaPortal\Shadow\Services\Portal;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use NinjaPortal\Portal\Contracts\Services\ApiProductServiceInterface;
use NinjaPortal\Portal\Contracts\Services\CategoryServiceInterface;

class ProductCatalogService
{
    public function __construct(
        protected ApiProductServiceInterface $apiProducts,
        protected CategoryServiceInterface $categories,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, ?Authenticatable $user = null): LengthAwarePaginator
    {
        $perPage = max(1, (int) ($filters['per_page'] ?? config('shadow-theme.catalog.per_page', 12)));
        $page = max(1, (int) ($filters['page'] ?? request()->integer('page', 1)));
        $scope = $this->normalizeScope((string) ($filters['scope'] ?? 'public'), $user);
        $query = $this->baseProductQuery();

        $this->applyVisibilityScope($query, $scope, $user);
        $this->applySearch($query);
        $this->applyCategoryFilter($query, $filters['category'] ?? null);

        return $query
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();
    }

    public function findVisibleBySlugOrFail(string $slug, ?Authenticatable $user = null, ?string $scope = null): Model
    {
        $query = $this->baseProductQuery();
        $this->applyVisibilityScope($query, $this->normalizeScope((string) ($scope ?? 'public'), $user), $user);
        $query->where('slug', $slug);

        return $query->firstOrFail();
    }

    /**
     * @return Collection<int, Model>
     */
    public function featured(?Authenticatable $user = null): Collection
    {
        $query = $this->baseProductQuery();
        $this->applyVisibilityScope($query, $user ? 'mine' : 'public', $user);

        return $query->orderByDesc('id')->limit(max(1, (int) config('shadow-theme.catalog.featured_limit', 6)))->get();
    }

    /**
     * @return Collection<int, Model>
     */
    public function categories(): Collection
    {
        return $this->categories->query()
            ->orderBy('id')
            ->get();
    }

    public function countMyVisible(?Authenticatable $user = null): int
    {
        $query = $this->baseProductQuery();
        $this->applyVisibilityScope($query, $user ? 'mine' : 'public', $user);

        return (int) $query->count();
    }

    /**
     * @return Collection<int, array{value:string,label:string,slug:?string}>
     */
    public function visibleProductOptions(?Authenticatable $user = null): Collection
    {
        $query = $this->baseProductQuery();
        $this->applyVisibilityScope($query, $user ? 'mine' : 'public', $user);

        return $query
            ->orderBy('id')
            ->get()
            ->map(function (Model $product): array {
                $value = (string) ($product->apigee_product_id ?? $product->slug ?? '');
                $label = trim((string) ($product->name ?? $product->slug ?? $value));

                return [
                    'value' => $value,
                    'label' => $label !== '' ? $label : $value,
                    'slug' => is_string($product->slug ?? null) ? $product->slug : null,
                ];
            })
            ->filter(fn (array $item) => $item['value'] !== '')
            ->values();
    }

    /**
     * @return Builder<Model>
     */
    protected function baseProductQuery(): Builder
    {
        return $this->apiProducts->query()->with(['categories']);
    }

    protected function applySearch(Builder $query): void
    {
        if (method_exists($query->getModel(), 'scopeSearch')) {
            $query->search();
            return;
        }

        $needle = trim((string) request()->query('q', ''));
        if ($needle === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($needle) {
            $builder->where('slug', 'like', '%'.$needle.'%')
                ->orWhere('apigee_product_id', 'like', '%'.$needle.'%');
        });
    }

    protected function applyCategoryFilter(Builder $query, mixed $category): void
    {
        $slug = is_string($category) ? trim($category) : '';
        if ($slug === '') {
            return;
        }

        $query->whereHas('categories', fn (Builder $builder) => $builder->where('slug', $slug));
    }

    protected function applyVisibilityScope(Builder $query, string $scope, ?Authenticatable $user): void
    {
        if ($scope !== 'mine' || ! $user) {
            $query->where('visibility', 'public');

            return;
        }

        $audienceIds = collect($user->audiences ?? [])->pluck('id')->values()->all();

        $query->whereIn('visibility', ['public', 'private'])
            ->where(function (Builder $builder) use ($audienceIds) {
                $builder->where('visibility', 'public')
                    ->orWhere(function (Builder $privateQuery) use ($audienceIds) {
                        $privateQuery->where('visibility', 'private')
                            ->where(function (Builder $audienceQuery) use ($audienceIds) {
                                $audienceQuery->whereHas('audiences', function (Builder $builder) use ($audienceIds) {
                                    if ($audienceIds === []) {
                                        $builder->whereRaw('1 = 0');

                                        return;
                                    }

                                    $builder->whereIn('audience_id', $audienceIds);
                                })->orWhereDoesntHave('audiences');
                            });
                    });
            });
    }

    protected function normalizeScope(string $scope, ?Authenticatable $user): string
    {
        $scope = Str::lower(trim($scope));

        return $scope === 'mine' && $user ? 'mine' : 'public';
    }
}
