<?php

namespace NinjaPortal\Shadow\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NinjaPortal\Shadow\Services\Auth\ShadowAuthManager;
use Symfony\Component\HttpFoundation\Response;

class RequireShadowThemeAuth
{
    public function __construct(protected ShadowAuthManager $auth) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->auth->check()) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('shadow.auth.login');
        }

        return $next($request);
    }
}
