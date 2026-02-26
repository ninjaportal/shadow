<?php

namespace NinjaPortal\Shadow\Services\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;
use NinjaPortal\Shadow\Services\Mfa\ShadowMfaService;

class ShadowAuthFlow
{
    public function __construct(
        protected ShadowAuthManager $auth,
        protected ShadowMfaService $mfa,
    ) {}

    /**
     * @return array{status:string,mfa_required?:bool,challenge?:array<string,mixed>,user?:Authenticatable}
     */
    public function login(string $email, string $password): array
    {
        $user = $this->auth->attemptCredentials($email, $password);
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [__('shadow-theme::shadow.messages.invalid_credentials')],
            ]);
        }

        if ($this->mfa->available() && $this->mfa->enabledForConsumer()) {
            $missingReason = $this->mfa->missingMfaReason($user, 'consumer');
            if (is_string($missingReason) && $missingReason !== '') {
                throw ValidationException::withMessages(['mfa' => [$missingReason]]);
            }

            if ($this->mfa->shouldChallengeOnLogin($user, 'consumer')) {
                $challenge = $this->mfa->createLoginChallenge($user, 'consumer');
                $this->setPendingMfaChallenge($challenge);

                return [
                    'status' => 'mfa_challenge',
                    'mfa_required' => true,
                    'challenge' => $challenge,
                ];
            }
        }

        $this->completeLogin($user);

        return [
            'status' => 'authenticated',
            'user' => $user,
        ];
    }

    public function logout(): void
    {
        $this->clearPendingMfaChallenge();
        $this->auth->logout();
    }

    public function hasPendingMfaChallenge(): bool
    {
        return is_array(session()->get($this->mfaSessionKey()));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pendingMfaChallenge(): ?array
    {
        $payload = session()->get($this->mfaSessionKey());

        return is_array($payload) ? $payload : null;
    }

    public function verifyPendingMfaChallenge(string $code): Authenticatable
    {
        $pending = $this->pendingMfaChallenge();
        if (! is_array($pending)) {
            throw ValidationException::withMessages([
                'challenge' => [__('shadow-theme::shadow.messages.no_pending_mfa_challenge')],
            ]);
        }

        $token = (string) ($pending['challenge_token'] ?? '');
        if ($token === '') {
            $this->clearPendingMfaChallenge();
            throw ValidationException::withMessages([
                'challenge' => [__('shadow-theme::shadow.messages.invalid_mfa_challenge_state')],
            ]);
        }

        $user = $this->mfa->verifyLoginChallenge($token, $code, 'consumer');
        $this->completeLogin($user);
        $this->clearPendingMfaChallenge();

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    public function resendPendingMfaChallenge(): array
    {
        $pending = $this->pendingMfaChallenge();
        if (! is_array($pending)) {
            throw ValidationException::withMessages([
                'challenge' => [__('shadow-theme::shadow.messages.no_pending_mfa_challenge')],
            ]);
        }

        $token = (string) ($pending['challenge_token'] ?? '');
        if ($token === '') {
            $this->clearPendingMfaChallenge();
            throw ValidationException::withMessages([
                'challenge' => [__('shadow-theme::shadow.messages.invalid_mfa_challenge_state')],
            ]);
        }

        $challenge = $this->mfa->resendLoginChallenge($token, 'consumer');
        $this->setPendingMfaChallenge($challenge);

        return $challenge;
    }

    protected function completeLogin(Authenticatable $user): void
    {
        session()->regenerate();
        $this->auth->login($user);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function setPendingMfaChallenge(array $payload): void
    {
        session()->put($this->mfaSessionKey(), $payload);
    }

    protected function clearPendingMfaChallenge(): void
    {
        session()->forget($this->mfaSessionKey());
    }

    protected function mfaSessionKey(): string
    {
        return (string) config('shadow-theme.auth.mfa_session_key', 'shadow_theme.mfa_login');
    }
}
