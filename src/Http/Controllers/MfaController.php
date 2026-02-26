<?php

namespace NinjaPortal\Shadow\Http\Controllers;

use Illuminate\Http\Request;
use NinjaPortal\Shadow\Services\Auth\ShadowAuthFlow;
use NinjaPortal\Shadow\Services\Mfa\ShadowMfaService;
use NinjaPortal\Shadow\Support\Theme;

class MfaController extends Controller
{
    public function challenge(ShadowAuthFlow $authFlow)
    {
        if (! $authFlow->hasPendingMfaChallenge()) {
            return redirect()->route('shadow.auth.login');
        }

        return view('shadow-theme::pages.auth.mfa-challenge', [
            'challenge' => $authFlow->pendingMfaChallenge(),
        ]);
    }

    public function verifyChallenge(Request $request, ShadowAuthFlow $authFlow, Theme $theme)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $authFlow->verifyPendingMfaChallenge((string) $data['code']);

        return redirect()->intended($theme->dashboardPath())
            ->with('shadow.success', __('shadow-theme::shadow.messages.signed_in_successfully'));
    }

    public function resendChallenge(ShadowAuthFlow $authFlow)
    {
        $authFlow->resendPendingMfaChallenge();

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.mfa_code_resent'));
    }

    public function settings(Request $request, ShadowMfaService $mfa)
    {
        $this->abortIfUnavailable($mfa);
        $user = $this->shadowUserOrFail($request);

        return view('shadow-theme::pages.mfa.settings', [
            'user' => $user,
            'mfaSettings' => $mfa->settingsPayload($user, 'consumer'),
            'emailOtpEnrollmentChallengeToken' => $request->session()->get($this->emailOtpEnrollmentSessionKey()),
        ]);
    }

    public function updateSettings(Request $request, ShadowMfaService $mfa)
    {
        $this->abortIfUnavailable($mfa);
        $user = $this->shadowUserOrFail($request);

        $attributes = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'preferred_driver' => ['nullable', 'string'],
        ]);

        $payload = [];
        if ($request->has('is_enabled')) {
            $payload['is_enabled'] = (bool) $attributes['is_enabled'];
        }
        if ($request->has('preferred_driver')) {
            $payload['preferred_driver'] = $attributes['preferred_driver'];
        }

        $mfa->updateSettings($user, $payload, 'consumer');

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.mfa_settings_updated'));
    }

    public function beginAuthenticator(Request $request, ShadowMfaService $mfa)
    {
        $this->abortIfUnavailable($mfa);
        $user = $this->shadowUserOrFail($request);
        $data = $request->validate(['label' => ['nullable', 'string', 'max:255']]);

        $setup = $mfa->beginAuthenticator($user, $data['label'] ?? null, 'consumer');

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.authenticator_setup_generated'))
            ->with('shadow.mfa.authenticator_setup', $setup);
    }

    public function confirmAuthenticator(Request $request, ShadowMfaService $mfa)
    {
        $this->abortIfUnavailable($mfa);
        $user = $this->shadowUserOrFail($request);
        $data = $request->validate(['code' => ['required', 'string', 'max:20']]);

        $mfa->confirmAuthenticator($user, (string) $data['code'], 'consumer');

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.authenticator_factor_enabled'));
    }

    public function disableAuthenticator(Request $request, ShadowMfaService $mfa)
    {
        $this->abortIfUnavailable($mfa);
        $mfa->disableFactor($this->shadowUserOrFail($request), 'authenticator', 'consumer');

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.authenticator_factor_disabled'));
    }

    public function beginEmailOtp(Request $request, ShadowMfaService $mfa)
    {
        $this->abortIfUnavailable($mfa);
        $user = $this->shadowUserOrFail($request);
        $payload = $mfa->beginEmailOtp($user, 'consumer');
        $token = data_get($payload, 'challenge.challenge_token');

        if (is_string($token) && $token !== '') {
            $request->session()->put($this->emailOtpEnrollmentSessionKey(), $token);
        }

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.email_otp_verification_sent'));
    }

    public function confirmEmailOtp(Request $request, ShadowMfaService $mfa)
    {
        $this->abortIfUnavailable($mfa);
        $user = $this->shadowUserOrFail($request);
        $data = $request->validate([
            'challenge_token' => ['nullable', 'string'],
            'code' => ['required', 'string', 'max:20'],
        ]);

        $token = trim((string) ($data['challenge_token'] ?? ''));
        if ($token === '') {
            $token = (string) $request->session()->get($this->emailOtpEnrollmentSessionKey(), '');
        }

        if ($token === '') {
            return back()->withErrors(['challenge_token' => __('shadow-theme::shadow.messages.start_email_otp_enrollment_first')]);
        }

        $mfa->confirmEmailOtp($user, $token, (string) $data['code'], 'consumer');
        $request->session()->forget($this->emailOtpEnrollmentSessionKey());

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.email_otp_factor_enabled'));
    }

    public function disableEmailOtp(Request $request, ShadowMfaService $mfa)
    {
        $this->abortIfUnavailable($mfa);
        $mfa->disableFactor($this->shadowUserOrFail($request), 'email_otp', 'consumer');

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.email_otp_factor_disabled'));
    }

    protected function abortIfUnavailable(ShadowMfaService $mfa): void
    {
        abort_unless($mfa->available() && $mfa->enabledForConsumer(), 404);
    }

    protected function emailOtpEnrollmentSessionKey(): string
    {
        return 'shadow_theme.mfa_email_otp_enrollment.challenge_token';
    }
}
