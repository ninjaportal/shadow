<?php

namespace NinjaPortal\Shadow\Http\Controllers\App;

use Illuminate\Http\Request;
use NinjaPortal\Portal\Contracts\Services\UserAppCredentialServiceInterface;
use NinjaPortal\Shadow\Http\Controllers\Controller;
use NinjaPortal\Shadow\Services\Portal\DeveloperAccessService;
use Throwable;

class CredentialsController extends Controller
{
    public function __construct(
        protected UserAppCredentialServiceInterface $credentials,
        protected DeveloperAccessService $developerAccess,
    ) {}

    public function store(Request $request, string $appName)
    {
        $user = $this->shadowUserOrFail($request);

        if (! $this->developerAccess->canCreateApps($user)) {
            return back()->withErrors(['credential' => __('shadow-theme::shadow.messages.account_pending_approval')]);
        }

        $data = $request->validate([
            'apiProducts' => ['required', 'array', 'min:1'],
            'apiProducts.*' => ['string'],
            'expiresIn' => ['nullable', 'integer'],
        ]);

        try {
            $this->credentials->create(
                (string) ($user->email ?? ''),
                $appName,
                array_values(array_filter((array) $data['apiProducts'], 'is_string')),
                isset($data['expiresIn']) ? (int) $data['expiresIn'] : null,
            );
        } catch (Throwable $e) {
            return back()->withErrors(['credential' => $e->getMessage()]);
        }

        return back()->with('shadow.success', __('shadow-theme::shadow.messages.credential_created'));
    }

    public function approve(Request $request, string $appName, string $key)
    {
        return $this->runAction($request, fn (string $email) => $this->credentials->approve($email, $appName, $key), __('shadow-theme::shadow.messages.credential_approved'));
    }

    public function revoke(Request $request, string $appName, string $key)
    {
        return $this->runAction($request, fn (string $email) => $this->credentials->revoke($email, $appName, $key), __('shadow-theme::shadow.messages.credential_revoked'));
    }

    public function destroy(Request $request, string $appName, string $key)
    {
        return $this->runAction($request, fn (string $email) => $this->credentials->delete($email, $appName, $key), __('shadow-theme::shadow.messages.credential_deleted'));
    }

    public function addProducts(Request $request, string $appName, string $key)
    {
        $data = $request->validate([
            'apiProducts' => ['required', 'array', 'min:1'],
            'apiProducts.*' => ['string'],
        ]);

        return $this->runAction(
            $request,
            fn (string $email) => $this->credentials->addProducts($email, $appName, $key, array_values(array_filter((array) $data['apiProducts'], 'is_string'))),
            __('shadow-theme::shadow.messages.credential_products_added')
        );
    }

    public function removeProduct(Request $request, string $appName, string $key, string $product)
    {
        return $this->runAction($request, fn (string $email) => $this->credentials->removeProducts($email, $appName, $key, $product), __('shadow-theme::shadow.messages.credential_product_removed'));
    }

    public function approveProduct(Request $request, string $appName, string $key, string $product)
    {
        return $this->runAction($request, fn (string $email) => $this->credentials->approveApiProduct($email, $appName, $key, $product), __('shadow-theme::shadow.messages.credential_product_approved'));
    }

    public function revokeProduct(Request $request, string $appName, string $key, string $product)
    {
        return $this->runAction($request, fn (string $email) => $this->credentials->revokeApiProduct($email, $appName, $key, $product), __('shadow-theme::shadow.messages.credential_product_revoked'));
    }

    protected function runAction(Request $request, callable $callback, string $successMessage)
    {
        $user = $this->shadowUserOrFail($request);

        try {
            $callback((string) ($user->email ?? ''));
        } catch (Throwable $e) {
            return back()->with('shadow.error', $e->getMessage());
        }

        return back()->with('shadow.success', $successMessage);
    }
}
