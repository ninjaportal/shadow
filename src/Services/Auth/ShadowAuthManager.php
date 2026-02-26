<?php

namespace NinjaPortal\Shadow\Services\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use NinjaPortal\Portal\Contracts\Services\UserServiceInterface;

class ShadowAuthManager
{
    protected ?Authenticatable $cachedUser = null;

    protected bool $resolved = false;

    public function __construct(protected UserServiceInterface $users) {}

    public function check(): bool
    {
        return $this->user() instanceof Authenticatable;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function id(): int|string|null
    {
        return session()->get($this->sessionKey());
    }

    public function user(): ?Authenticatable
    {
        if ($this->resolved) {
            return $this->cachedUser;
        }

        $this->resolved = true;
        $id = $this->id();
        if ($id === null || $id === '') {
            return $this->cachedUser = null;
        }

        $user = $this->users->find($id);

        return $this->cachedUser = $user instanceof Authenticatable ? $user : null;
    }

    public function login(Authenticatable $user): void
    {
        session()->put($this->sessionKey(), $user->getAuthIdentifier());
        $this->cachedUser = $user;
        $this->resolved = true;
    }

    public function logout(): void
    {
        session()->forget($this->sessionKey());
        $this->cachedUser = null;
        $this->resolved = true;
    }

    public function attemptCredentials(string $email, string $password): ?Authenticatable
    {
        $user = $this->findByEmail($email);

        if (! $user) {
            return null;
        }

        $hash = (string) ($user->password ?? '');
        if ($hash === '' || ! Hash::check($password, $hash)) {
            return null;
        }

        return $user;
    }

    public function findByEmail(string $email): ?Authenticatable
    {
        $normalized = trim(strtolower($email));
        if ($normalized === '') {
            return null;
        }

        $user = $this->users->findByEmail($normalized);

        return $user instanceof Authenticatable ? $user : null;
    }

    /**
     * @return class-string<Model>
     */
    public function userModelClass(): string
    {
        $model = $this->users->query()->getModel();

        return $model::class;
    }

    protected function sessionKey(): string
    {
        return (string) config('shadow-theme.auth.session_key', 'shadow_theme.user_id');
    }
}
