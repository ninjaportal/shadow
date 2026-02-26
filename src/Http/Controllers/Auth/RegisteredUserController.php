<?php

namespace NinjaPortal\Shadow\Http\Controllers\Auth;

use Closure;
use Illuminate\Http\Request;
use NinjaPortal\Portal\Contracts\Services\UserServiceInterface;
use NinjaPortal\Shadow\Http\Controllers\Controller;
use NinjaPortal\Shadow\Services\Auth\ShadowAuthManager;
use NinjaPortal\Shadow\Support\Theme;

class RegisteredUserController extends Controller
{
    public function create()
    {
        abort_unless((bool) config('shadow-theme.features.registration', true), 404);

        return view('shadow-theme::pages.auth.register');
    }

    public function store(Request $request, UserServiceInterface $users, ShadowAuthManager $auth, Theme $theme)
    {
        abort_unless((bool) config('shadow-theme.features.registration', true), 404);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($users): void {
                    if (! is_string($value)) {
                        return;
                    }

                    if ($users->findByEmail($value) !== null) {
                        $fail(__('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $user = $users->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => strtolower(trim((string) $data['email'])),
            'password' => $data['password'],
        ]);

        $request->session()->regenerate();
        $auth->login($user);

        return redirect()->intended($theme->dashboardPath())
            ->with('shadow.success', __('shadow-theme::shadow.messages.account_created_successfully'));
    }
}
