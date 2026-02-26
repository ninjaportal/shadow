<?php

namespace NinjaPortal\Shadow\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use NinjaPortal\Portal\Contracts\Services\UserServiceInterface;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('shadow-theme::pages.profile.edit', [
            'user' => $this->shadowUserOrFail($request),
        ]);
    }

    public function update(Request $request, UserServiceInterface $users)
    {
        $user = $this->shadowUserOrFail($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
        ]);

        $users->update($user, [
            'first_name' => (string) $data['first_name'],
            'last_name' => (string) ($data['last_name'] ?? ''),
        ]);

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.profile_updated'));
    }

    public function updatePassword(Request $request, UserServiceInterface $users)
    {
        $user = $this->shadowUserOrFail($request);

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        try {
            $users->updatePassword($user, (string) $data['current_password'], (string) $data['password']);
        } catch (Throwable $e) {
            return back()->withErrors([
                'current_password' => $e instanceof Exception ? $e->getMessage() : __('shadow-theme::shadow.messages.failed_to_update_password'),
            ]);
        }

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.password_updated'));
    }
}
