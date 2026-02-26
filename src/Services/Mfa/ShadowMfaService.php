<?php

namespace NinjaPortal\Shadow\Services\Mfa;

use Illuminate\Contracts\Auth\Authenticatable;
use RuntimeException;

class ShadowMfaService
{
    public function available(): bool
    {
        if (! (bool) config('shadow-theme.features.mfa.ui_enabled', true)) {
            return false;
        }

        if (! (bool) config('portal-mfa.enabled', false)) {
            return false;
        }

        return $this->hasRequiredBindings();
    }

    public function enabledForConsumer(): bool
    {
        if (! $this->available()) {
            return false;
        }

        return (bool) $this->actorConfigService()->actorEnabled('consumer');
    }

    public function requiresMfa(Authenticatable $user, string $context = 'consumer'): bool
    {
        if (! $this->available()) {
            return false;
        }

        return (bool) $this->profileService()->requiresMfa($user, $this->normalizeContext($context));
    }

    public function shouldChallengeOnLogin(Authenticatable $user, string $context = 'consumer'): bool
    {
        if (! $this->available()) {
            return false;
        }

        return (bool) $this->profileService()->shouldChallengeOnLogin($user, $this->normalizeContext($context));
    }

    /**
     * @return array<string, mixed>
     */
    public function createLoginChallenge(Authenticatable $user, string $context = 'consumer'): array
    {
        $this->assertAvailable();

        return $this->webLoginChallengeService()->createLoginChallenge($user, $this->normalizeContext($context));
    }

    public function verifyLoginChallenge(string $challengeToken, string $code, string $context = 'consumer'): Authenticatable
    {
        $this->assertAvailable();

        return $this->webLoginChallengeService()->verifyLoginChallenge($this->normalizeContext($context), $challengeToken, $code);
    }

    /**
     * @return array<string, mixed>
     */
    public function resendLoginChallenge(string $challengeToken, string $context = 'consumer'): array
    {
        $this->assertAvailable();

        return $this->webLoginChallengeService()->resendLoginChallenge($this->normalizeContext($context), $challengeToken);
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsPayload(Authenticatable $user, string $context = 'consumer'): array
    {
        $this->assertAvailable();

        return $this->profileService()->getSettingsPayload($user, $this->normalizeContext($context));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function updateSettings(Authenticatable $user, array $attributes, string $context = 'consumer'): array
    {
        $this->assertAvailable();

        return $this->profileService()->updateSettings($user, $this->normalizeContext($context), $attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function beginAuthenticator(Authenticatable $user, ?string $label = null, string $context = 'consumer'): array
    {
        $this->assertAvailable();

        return $this->factorService()->beginAuthenticatorEnrollment($user, $this->normalizeContext($context), $label);
    }

    /**
     * @return array<string, mixed>
     */
    public function confirmAuthenticator(Authenticatable $user, string $code, string $context = 'consumer'): array
    {
        $this->assertAvailable();

        return $this->factorService()->confirmAuthenticatorEnrollment($user, $this->normalizeContext($context), $code);
    }

    /**
     * @return array<string, mixed>
     */
    public function beginEmailOtp(Authenticatable $user, string $context = 'consumer'): array
    {
        $this->assertAvailable();

        return $this->factorService()->beginEmailOtpEnrollment($user, $this->normalizeContext($context));
    }

    /**
     * @return array<string, mixed>
     */
    public function confirmEmailOtp(Authenticatable $user, string $challengeToken, string $code, string $context = 'consumer'): array
    {
        $this->assertAvailable();

        return $this->factorService()->confirmEmailOtpEnrollment($user, $this->normalizeContext($context), $challengeToken, $code);
    }

    public function disableFactor(Authenticatable $user, string $driver, string $context = 'consumer'): void
    {
        $this->assertAvailable();
        $this->factorService()->disableFactor($user, $this->normalizeContext($context), $driver);
    }

    public function missingMfaReason(Authenticatable $user, string $context = 'consumer'): ?string
    {
        if (! $this->available()) {
            return null;
        }

        if ($this->requiresMfa($user, $context) && ! $this->shouldChallengeOnLogin($user, $context)) {
            return __('shadow-theme::shadow.messages.mfa_required_no_factor');
        }

        return null;
    }

    protected function hasRequiredBindings(): bool
    {
        foreach ([
            'NinjaPortal\\Mfa\\Contracts\\Services\\MfaProfileServiceInterface',
            'NinjaPortal\\Mfa\\Contracts\\Services\\MfaFactorServiceInterface',
            'NinjaPortal\\Mfa\\Contracts\\Services\\MfaActorConfigServiceInterface',
        ] as $abstract) {
            if (! interface_exists($abstract) || ! app()->bound($abstract)) {
                return false;
            }
        }

        return true;
    }

    protected function assertAvailable(): void
    {
        if (! $this->available()) {
            throw new RuntimeException(__('shadow-theme::shadow.messages.portal_mfa_not_available'));
        }
    }

    protected function profileService(): mixed
    {
        return app('NinjaPortal\\Mfa\\Contracts\\Services\\MfaProfileServiceInterface');
    }

    protected function factorService(): mixed
    {
        return app('NinjaPortal\\Mfa\\Contracts\\Services\\MfaFactorServiceInterface');
    }

    protected function actorConfigService(): mixed
    {
        return app('NinjaPortal\\Mfa\\Contracts\\Services\\MfaActorConfigServiceInterface');
    }

    protected function webLoginChallengeService(): ShadowMfaLoginChallengeService
    {
        return app(ShadowMfaLoginChallengeService::class);
    }

    protected function normalizeContext(string $context): string
    {
        return strtolower(trim($context)) === 'admin' ? 'admin' : 'consumer';
    }
}
