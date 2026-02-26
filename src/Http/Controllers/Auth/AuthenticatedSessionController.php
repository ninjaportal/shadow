<?php

namespace NinjaPortal\Shadow\Http\Controllers\Auth;

use Illuminate\Http\Request;
use NinjaPortal\Shadow\Http\Controllers\Controller;
use NinjaPortal\Shadow\Services\Auth\ShadowAuthFlow;
use NinjaPortal\Shadow\Support\Theme;

class AuthenticatedSessionController extends Controller
{
    public function create(ShadowAuthFlow $authFlow)
    {
        if ($authFlow->hasPendingMfaChallenge()) {
            return redirect()->route('shadow.auth.mfa.challenge');
        }

        return view('shadow-theme::pages.auth.login');
    }

    public function store(Request $request, ShadowAuthFlow $authFlow, Theme $theme)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $authFlow->login((string) $data['email'], (string) $data['password']);

        if (($result['status'] ?? null) === 'mfa_challenge') {
            return redirect()->route('shadow.auth.mfa.challenge')
                ->with('shadow.success', __('shadow-theme::shadow.messages.enter_mfa_code'));
        }

        return redirect()->intended($theme->dashboardPath())
            ->with('shadow.success', __('shadow-theme::shadow.messages.welcome_back'));
    }

    public function destroy(Request $request, ShadowAuthFlow $authFlow)
    {
        $authFlow->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shadow.home')
            ->with('shadow.success', __('shadow-theme::shadow.messages.signed_out_successfully'));
    }
}
