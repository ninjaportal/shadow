<?php

namespace NinjaPortal\Shadow\Http\Controllers;

use Illuminate\Http\Request;
use NinjaPortal\Portal\Contracts\Services\UserAppServiceInterface;
use NinjaPortal\Shadow\Services\Portal\DeveloperAccessService;
use NinjaPortal\Shadow\Services\Portal\ProductCatalogService;
use NinjaPortal\Shadow\Support\ApigeeEntityPresenter;
use Throwable;

class AppsController extends Controller
{
    public function __construct(
        protected UserAppServiceInterface $apps,
        protected DeveloperAccessService $developerAccess,
        protected ProductCatalogService $catalog,
        protected ApigeeEntityPresenter $presenter,
    ) {}

    public function index(Request $request)
    {
        $user = $this->shadowUserOrFail($request);
        $apps = collect();
        $error = null;

        try {
            $apps = $this->apps->all((string) ($user->email ?? ''))
                ->map(fn ($app) => $this->presenter->presentApp($app));
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('shadow-theme::pages.apps.index', [
            'user' => $user,
            'apps' => $apps,
            'appsError' => $error,
            'canCreateApps' => $this->developerAccess->canCreateApps($user),
        ]);
    }

    public function create(Request $request)
    {
        $user = $this->shadowUserOrFail($request);

        return view('shadow-theme::pages.apps.create', [
            'user' => $user,
            'canCreateApps' => $this->developerAccess->canCreateApps($user),
            'productOptions' => $this->catalog->visibleProductOptions($user),
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->shadowUserOrFail($request);

        if (! $this->developerAccess->canCreateApps($user)) {
            return back()->withErrors(['app' => __('shadow-theme::shadow.messages.account_pending_approval')])->withInput();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'callbackUrl' => ['nullable', 'url', 'max:2000'],
            'apiProducts' => ['sometimes', 'array'],
            'apiProducts.*' => ['string'],
        ]);

        try {
            $this->apps->create((string) ($user->email ?? ''), [
                'name' => (string) $data['name'],
                'callbackUrl' => $data['callbackUrl'] ?? null,
                'apiProducts' => array_values(array_filter((array) ($data['apiProducts'] ?? []), 'is_string')),
            ]);
        } catch (Throwable $e) {
            return back()->withErrors(['app' => $e->getMessage()])->withInput();
        }

        return redirect()->route('shadow.apps.show', ['appName' => (string) $data['name']])
            ->with('shadow.success', __('shadow-theme::shadow.messages.app_created'));
    }

    public function show(Request $request, string $appName)
    {
        $user = $this->shadowUserOrFail($request);

        try {
            $app = $this->apps->find((string) ($user->email ?? ''), $appName);
        } catch (Throwable $e) {
            return redirect()->route('shadow.apps.index')->with('shadow.error', $e->getMessage());
        }

        abort_if($app === null, 404);

        return view('shadow-theme::pages.apps.show', [
            'user' => $user,
            'appEntity' => $app,
            'appData' => $this->presenter->presentApp($app),
            'productOptions' => $this->catalog->visibleProductOptions($user),
            'canCreateApps' => $this->developerAccess->canCreateApps($user),
        ]);
    }

    public function update(Request $request, string $appName)
    {
        $user = $this->shadowUserOrFail($request);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'callbackUrl' => ['nullable', 'url', 'max:2000'],
        ]);

        try {
            $updated = $this->apps->update((string) ($user->email ?? ''), $appName, $data);
            $presented = $updated ? $this->presenter->presentApp($updated) : ['name' => $appName];
            $target = (string) ($presented['name'] ?? $appName);
        } catch (Throwable $e) {
            return back()->withErrors(['app' => $e->getMessage()])->withInput();
        }

        return redirect()->route('shadow.apps.show', ['appName' => $target])
            ->with('shadow.success', __('shadow-theme::shadow.messages.app_updated'));
    }

    public function destroy(Request $request, string $appName)
    {
        $user = $this->shadowUserOrFail($request);

        try {
            $this->apps->delete((string) ($user->email ?? ''), $appName);
        } catch (Throwable $e) {
            return back()->with('shadow.error', $e->getMessage());
        }

        return redirect()->route('shadow.apps.index')->with('shadow.success', __('shadow-theme::shadow.messages.app_deleted'));
    }

    public function approve(Request $request, string $appName)
    {
        return $this->appLifecycleAction($request, $appName, 'approve', __('shadow-theme::shadow.messages.app_approved'));
    }

    public function revoke(Request $request, string $appName)
    {
        return $this->appLifecycleAction($request, $appName, 'revoke', __('shadow-theme::shadow.messages.app_revoked'));
    }

    protected function appLifecycleAction(Request $request, string $appName, string $method, string $message)
    {
        $user = $this->shadowUserOrFail($request);

        try {
            $this->apps->{$method}((string) ($user->email ?? ''), $appName);
        } catch (Throwable $e) {
            return back()->with('shadow.error', $e->getMessage());
        }

        return back()->with('shadow.success', $message);
    }
}
