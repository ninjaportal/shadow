<?php

namespace NinjaPortal\Shadow\Http\Controllers;

use Illuminate\Http\Request;
use NinjaPortal\Portal\Contracts\Services\UserAppServiceInterface;
use NinjaPortal\Shadow\Services\Mfa\ShadowMfaService;
use NinjaPortal\Shadow\Services\Portal\ProductCatalogService;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        UserAppServiceInterface $apps,
        ProductCatalogService $catalog,
        ShadowMfaService $mfa,
    ) {
        $user = $this->shadowUserOrFail($request);
        $appsCount = 0;
        $recentApps = collect();

        try {
            $allApps = $apps->all((string) ($user->email ?? ''));
            $appsCount = $allApps->count();
            $recentApps = $allApps->take(5);
        } catch (Throwable) {
            // Keep dashboard usable if Apigee is temporarily unavailable.
        }

        return view('shadow-theme::pages.dashboard.index', [
            'user' => $user,
            'appsCount' => $appsCount,
            'visibleProductsCount' => $catalog->countMyVisible($user),
            'recentApps' => $recentApps,
            'mfaSettings' => $mfa->available() ? $mfa->settingsPayload($user, 'consumer') : null,
        ]);
    }
}
