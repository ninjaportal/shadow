<?php

namespace NinjaPortal\Shadow\Services\Mfa;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;
use NinjaPortal\Mfa\Contracts\Repositories\MfaChallengeRepositoryInterface;
use NinjaPortal\Mfa\Contracts\Repositories\MfaFactorRepositoryInterface;
use NinjaPortal\Mfa\Contracts\Services\MfaActorConfigServiceInterface;
use NinjaPortal\Mfa\Contracts\Services\MfaDriverManagerInterface;
use NinjaPortal\Mfa\Contracts\Services\MfaProfileServiceInterface;
use NinjaPortal\Mfa\Models\MfaChallenge;
use NinjaPortal\Mfa\Models\MfaFactor;
use NinjaPortal\Mfa\Support\MfaTokenHasher;
use RuntimeException;

class ShadowMfaLoginChallengeService
{
    public const PURPOSE = 'shadow_theme_login';

    public function __construct(
        protected MfaChallengeRepositoryInterface $challenges,
        protected MfaFactorRepositoryInterface $factors,
        protected MfaDriverManagerInterface $drivers,
        protected MfaProfileServiceInterface $profiles,
        protected MfaActorConfigServiceInterface $actors,
        protected MfaTokenHasher $hasher,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function createLoginChallenge(Authenticatable $actor, string $context = 'consumer'): array
    {
        $context = $this->normalizeContext($context);
        $factor = $this->selectChallengeFactor($actor, $context);

        if (! $factor instanceof MfaFactor) {
            throw new RuntimeException(__('shadow-theme::shadow.messages.no_eligible_mfa_factor'));
        }

        $driver = $this->drivers->driver($factor->driver);
        $token = $this->hasher->makeToken((int) config('portal-mfa.challenge.token_length', 64));
        $ttl = (int) config('portal-mfa.challenge.login_ttl_seconds', 300);
        $maxAttempts = $factor->driver === 'email_otp'
            ? (int) config('portal-mfa.drivers.email_otp.max_attempts', 5)
            : 5;
        $maxResends = $factor->driver === 'email_otp'
            ? (int) config('portal-mfa.drivers.email_otp.max_resends', 3)
            : 0;

        $this->challenges->invalidateOpenChallengesForActorDriver($actor, $factor->driver, self::PURPOSE);

        $challenge = $this->challenges->createForActor($actor, $factor, [
            'token_hash' => $this->hasher->hash($token),
            'context' => $context,
            'purpose' => self::PURPOSE,
            'expires_at' => now()->addSeconds(max(30, $ttl)),
            'max_attempts' => max(1, $maxAttempts),
            'max_resends' => max(0, $maxResends),
            'payload' => [],
        ]);

        try {
            $driverPayload = $driver->prepareChallenge($challenge, $factor, $actor, $context);
        } catch (\Throwable $e) {
            $challenge->invalidated_at = now();
            $this->challenges->save($challenge);
            throw $e;
        }

        $driverPayload = $this->sanitizeDriverPayload($driverPayload);

        return array_merge([
            'mfa_required' => true,
            'challenge_type' => 'login',
            'challenge_token' => $token,
            'context' => $context,
        ], $driverPayload);
    }

    public function verifyLoginChallenge(string $context, string $challengeToken, string $code): Authenticatable
    {
        $challenge = $this->resolvePendingChallenge($context, $challengeToken);
        $actor = $this->resolveChallengeActor($challenge);
        $factor = $challenge->factor;

        if (! $factor instanceof MfaFactor) {
            throw new AuthenticationException(__('shadow-theme::shadow.messages.mfa_factor_not_found_for_challenge'));
        }

        $this->ensureAttemptsRemaining($challenge, 'code');

        $driver = $this->drivers->driver($factor->driver);
        if (! $driver->verifyChallenge($challenge, $factor, $code, $actor, $this->normalizeContext($context))) {
            $challenge->attempts = (int) $challenge->attempts + 1;
            if ((int) $challenge->attempts >= (int) $challenge->max_attempts) {
                $challenge->invalidated_at = now();
            }
            $this->challenges->save($challenge);

            throw ValidationException::withMessages(['code' => [__('shadow-theme::shadow.messages.invalid_verification_code')]]);
        }

        $challenge->completed_at = now();
        $this->challenges->save($challenge);

        $this->factors->save($factor, ['last_used_at' => now()]);

        return $actor;
    }

    /**
     * @return array<string, mixed>
     */
    public function resendLoginChallenge(string $context, string $challengeToken): array
    {
        $challenge = $this->resolvePendingChallenge($context, $challengeToken);
        $actor = $this->resolveChallengeActor($challenge);
        $factor = $challenge->factor;

        if (! $factor instanceof MfaFactor) {
            throw new AuthenticationException(__('shadow-theme::shadow.messages.mfa_factor_not_found_for_challenge'));
        }

        $driver = $this->drivers->driver($factor->driver);
        if (! $driver->supportsResend()) {
            throw ValidationException::withMessages([
                'challenge_token' => [__('shadow-theme::shadow.messages.mfa_driver_no_resend')],
            ]);
        }

        $payload = $driver->resendChallenge($challenge, $factor, $actor, $this->normalizeContext($context));
        $payload = $this->sanitizeDriverPayload($payload);
        $payload['challenge_token'] = $challengeToken;
        $payload['challenge_type'] = 'login';
        $payload['mfa_required'] = true;

        return $payload;
    }

    protected function selectChallengeFactor(Authenticatable $actor, string $context): ?MfaFactor
    {
        $allowedDrivers = $this->actors->actorAllowedDrivers($context);
        $all = $this->factors->listForActor($actor)
            ->filter(fn (MfaFactor $factor) => $factor->is_enabled && $factor->is_verified && in_array($factor->driver, $allowedDrivers, true))
            ->values();

        if ($all->isEmpty()) {
            return null;
        }

        $primary = $all->first(fn (MfaFactor $factor) => (bool) $factor->is_primary);
        if ($primary instanceof MfaFactor) {
            return $primary;
        }

        $profile = $this->profiles->getSettingsPayload($actor, $context);
        $preferredDriver = data_get($profile, 'profile.preferred_driver');
        if (is_string($preferredDriver) && $preferredDriver !== '') {
            $preferred = $all->first(fn (MfaFactor $factor) => $factor->driver === $preferredDriver);
            if ($preferred instanceof MfaFactor) {
                return $preferred;
            }
        }

        $ordered = collect($allowedDrivers)
            ->map(fn (string $driverKey) => $all->first(fn (MfaFactor $factor) => $factor->driver === $driverKey))
            ->first(fn ($factor) => $factor instanceof MfaFactor);

        return $ordered instanceof MfaFactor ? $ordered : $all->first();
    }

    protected function resolvePendingChallenge(string $context, string $token): MfaChallenge
    {
        $tokenHash = $this->hasher->hash(trim($token));
        $challenge = $this->challenges->findPendingByTokenHash($tokenHash, $this->normalizeContext($context), self::PURPOSE);
        if (! $challenge instanceof MfaChallenge) {
            throw ValidationException::withMessages(['challenge_token' => [__('shadow-theme::shadow.messages.invalid_or_expired_mfa_challenge')]]);
        }

        $challenge->loadMissing('factor', 'authenticatable');

        return $challenge;
    }

    protected function resolveChallengeActor(MfaChallenge $challenge): Authenticatable
    {
        $actor = $challenge->authenticatable;
        if (! $actor instanceof Authenticatable) {
            throw new AuthenticationException(__('shadow-theme::shadow.messages.challenge_actor_not_resolved'));
        }

        return $actor;
    }

    protected function ensureAttemptsRemaining(MfaChallenge $challenge, string $field): void
    {
        if ((int) $challenge->attempts >= (int) $challenge->max_attempts) {
            throw ValidationException::withMessages([$field => [__('shadow-theme::shadow.messages.maximum_verification_attempts_reached')]]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function sanitizeDriverPayload(array $payload): array
    {
        unset($payload['_internal_plain_code']);

        return $payload;
    }

    protected function normalizeContext(string $context): string
    {
        return strtolower(trim($context)) === 'admin' ? 'admin' : 'consumer';
    }
}
