<?php

namespace NinjaPortal\Shadow;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use NinjaPortal\Shadow\Commands\InstallCommand;
use NinjaPortal\Shadow\Http\Middleware\RedirectIfShadowThemeAuthenticated;
use NinjaPortal\Shadow\Http\Middleware\RequireShadowThemeAuth;
use NinjaPortal\Shadow\Http\Middleware\ResolveShadowThemeUser;
use NinjaPortal\Shadow\Services\Auth\ShadowAuthFlow;
use NinjaPortal\Shadow\Services\Auth\ShadowAuthManager;
use NinjaPortal\Shadow\Services\Mfa\ShadowMfaService;
use NinjaPortal\Shadow\Services\Portal\DeveloperAccessService;
use NinjaPortal\Shadow\Services\Portal\ProductCatalogService;
use NinjaPortal\Shadow\Support\ApigeeEntityPresenter;
use NinjaPortal\Shadow\Support\QrCodeRenderer;
use NinjaPortal\Shadow\Support\Theme;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ShadowServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('shadow-theme')
            ->hasConfigFile('shadow-theme')
            ->hasViews()
            ->hasRoute('web')
            ->hasCommand(InstallCommand::class);
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(Theme::class);
        $this->app->singleton(ShadowAuthManager::class);
        $this->app->singleton(ShadowMfaService::class);
        $this->app->singleton(ShadowAuthFlow::class);
        $this->app->singleton(ProductCatalogService::class);
        $this->app->singleton(DeveloperAccessService::class);
        $this->app->singleton(ApigeeEntityPresenter::class);
        $this->app->singleton(QrCodeRenderer::class);
    }

    public function bootingPackage(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'shadow-theme');

        $router = $this->app['router'];
        $router->aliasMiddleware('shadow.user', ResolveShadowThemeUser::class);
        $router->aliasMiddleware('shadow.auth', RequireShadowThemeAuth::class);
        $router->aliasMiddleware('shadow.guest', RedirectIfShadowThemeAuthenticated::class);

        Blade::anonymousComponentNamespace('shadow-theme::components', 'shadow');

        View::composer('shadow-theme::*', function ($view): void {
            $theme = app(Theme::class);
            $forcedLocale = trim((string) config('shadow-theme.localization.force_locale', ''));
            if ($forcedLocale !== '') {
                app()->setLocale($forcedLocale);
            }
            $auth = app(ShadowAuthManager::class);
            $shadowUser = $auth->user();

            $view->with('shadowTheme', [
                'theme' => $theme,
                'locale' => $theme->locale(),
                'direction' => $theme->direction(),
                'isRtl' => $theme->isRtl(),
                'branding' => $theme->branding(),
                'palette' => $theme->palette(),
                'routePrefix' => $theme->routePrefix(),
                'user' => $shadowUser,
                'isAuthenticated' => $shadowUser !== null,
                'mfa' => app(ShadowMfaService::class),
            ]);
        });
    }
}
