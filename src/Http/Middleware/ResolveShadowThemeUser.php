<?php

namespace NinjaPortal\Shadow\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NinjaPortal\Shadow\Services\Auth\ShadowAuthManager;
use Symfony\Component\HttpFoundation\Response;

class ResolveShadowThemeUser
{
    public function __construct(protected ShadowAuthManager $auth) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->auth->user();
        $request->attributes->set('shadowUser', $user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
