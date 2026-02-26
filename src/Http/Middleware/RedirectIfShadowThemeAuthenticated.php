<?php

namespace NinjaPortal\Shadow\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NinjaPortal\Shadow\Services\Auth\ShadowAuthManager;
use NinjaPortal\Shadow\Support\Theme;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfShadowThemeAuthenticated
{
    public function __construct(
        protected ShadowAuthManager $auth,
        protected Theme $theme,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->check()) {
            return redirect($this->theme->dashboardPath());
        }

        return $next($request);
    }
}
