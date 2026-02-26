<?php

namespace NinjaPortal\Shadow\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use NinjaPortal\Shadow\Support\Theme;

abstract class Controller extends BaseController
{
    protected function shadowUser(Request $request): ?Authenticatable
    {
        $user = $request->attributes->get('shadowUser');

        return $user instanceof Authenticatable ? $user : null;
    }

    protected function shadowUserOrFail(Request $request): Authenticatable
    {
        $user = $this->shadowUser($request);
        abort_unless($user instanceof Authenticatable, 401);

        return $user;
    }

    protected function redirectToDashboard(Theme $theme): RedirectResponse
    {
        return redirect($theme->dashboardPath());
    }
}
