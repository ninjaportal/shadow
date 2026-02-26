<?php

namespace NinjaPortal\Shadow\Http\Controllers\Auth;

use Illuminate\Http\Request;
use NinjaPortal\Portal\Contracts\Services\UserServiceInterface;
use NinjaPortal\Shadow\Http\Controllers\Controller;
use Throwable;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        abort_unless((bool) config('shadow-theme.features.password_reset', true), 404);

        return view('shadow-theme::pages.auth.forgot-password');
    }

    public function store(Request $request, UserServiceInterface $users)
    {
        abort_unless((bool) config('shadow-theme.features.password_reset', true), 404);

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $users->requestPasswordReset((string) $data['email']);
        } catch (Throwable) {
            // Prevent email enumeration.
        }

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.reset_link_if_account_exists'));
    }
}
