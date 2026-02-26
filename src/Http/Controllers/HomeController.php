<?php

namespace NinjaPortal\Shadow\Http\Controllers;

use Illuminate\Http\Request;
use NinjaPortal\Shadow\Services\Portal\ProductCatalogService;

class HomeController extends Controller
{
    public function __invoke(Request $request, ProductCatalogService $catalog)
    {
        $user = $this->shadowUser($request);

        return view('shadow-theme::pages.home.index', [
            'featuredProducts' => $catalog->featured($user),
            'categories' => $catalog->categories()->take(8),
            'visibleProductCount' => $catalog->countMyVisible($user),
        ]);
    }
}
