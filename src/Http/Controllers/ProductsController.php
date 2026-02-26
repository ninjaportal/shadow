<?php

namespace NinjaPortal\Shadow\Http\Controllers;

use Illuminate\Http\Request;
use NinjaPortal\Shadow\Services\Portal\ProductCatalogService;

class ProductsController extends Controller
{
    public function index(Request $request, ProductCatalogService $catalog)
    {
        $user = $this->shadowUser($request);
        $scope = $request->query('scope', $user ? 'mine' : 'public');

        return view('shadow-theme::pages.products.index', [
            'products' => $catalog->paginate([
                'scope' => $scope,
                'category' => $request->query('category'),
                'page' => $request->integer('page', 1),
                'per_page' => config('shadow-theme.catalog.per_page', 12),
            ], $user),
            'categories' => $catalog->categories(),
            'scope' => $user ? (string) $scope : 'public',
        ]);
    }

    public function show(Request $request, string $slug, ProductCatalogService $catalog)
    {
        $user = $this->shadowUser($request);
        $scope = $request->query('scope', $user ? 'mine' : 'public');

        return view('shadow-theme::pages.products.show', [
            'product' => $catalog->findVisibleBySlugOrFail($slug, $user, is_string($scope) ? $scope : null),
            'scope' => $user ? (string) $scope : 'public',
        ]);
    }
}
