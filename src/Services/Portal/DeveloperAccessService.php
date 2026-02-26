<?php

namespace NinjaPortal\Shadow\Services\Portal;

use NinjaPortal\Portal\Contracts\Services\SettingServiceInterface;

class DeveloperAccessService
{
    public function __construct(protected SettingServiceInterface $settings) {}

    public function canCreateApps(mixed $user): bool
    {
        if (! is_object($user)) {
            return false;
        }

        $allowUnapproved = (bool) $this->settings->get('features.allow_unapproved_app_creation');
        if ($allowUnapproved) {
            return true;
        }

        $activeStatus = 'active';
        $class = $user::class;

        if (defined($class.'::ACTIVE_STATUS')) {
            $activeStatus = (string) constant($class.'::ACTIVE_STATUS');
        } elseif (property_exists($class, 'ACTIVE_STATUS')) {
            /** @phpstan-ignore-next-line */
            $activeStatus = (string) $user::$ACTIVE_STATUS;
        }

        return (string) ($user->status ?? '') === $activeStatus;
    }
}
