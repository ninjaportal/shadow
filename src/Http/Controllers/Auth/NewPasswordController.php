<?php

namespace NinjaPortal\Shadow\Http\Controllers\Auth;

use Illuminate\Http\Request;
use NinjaPortal\Portal\Contracts\Services\UserServiceInterface;
use NinjaPortal\Shadow\Http\Controllers\Controller;

class NewPasswordController extends Controller
{
    public function create(Request $request)
    {
        abort_unless((bool) config('shadow-theme.features.password_reset', true), 404);

        return view('shadow-theme::pages.auth.reset-password', [
            'token' => (string) $request->query('token', ''),
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(Request $request, UserServiceInterface $users)
    {
        abort_unless((bool) config('shadow-theme.features.password_reset', true), 404);

        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $ok = $users->resetPassword(
            (string) $data['email'],
            (string) $data['password'],
                (string) $data['token'],
        );

        if (! $ok) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['token' => __('shadow-theme::shadow.messages.invalid_or_expired_reset_token')]);
        }

        return redirect()->route('shadow.auth.login')
            ->with('shadow.success', __('shadow-theme::shadow.messages.password_reset_successfully'));
    }
}
