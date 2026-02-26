<?php

use Illuminate\Support\Facades\Route;
use NinjaPortal\Shadow\Http\Controllers\App\CredentialsController;
use NinjaPortal\Shadow\Http\Controllers\AppsController;
use NinjaPortal\Shadow\Http\Controllers\DashboardController;
use NinjaPortal\Shadow\Http\Controllers\HomeController;
use NinjaPortal\Shadow\Http\Controllers\MfaController;
use NinjaPortal\Shadow\Http\Controllers\ProductsController;
use NinjaPortal\Shadow\Http\Controllers\ProfileController;
use NinjaPortal\Shadow\Http\Controllers\Auth\AuthenticatedSessionController;
use NinjaPortal\Shadow\Http\Controllers\Auth\NewPasswordController;
use NinjaPortal\Shadow\Http\Controllers\Auth\PasswordResetLinkController;
use NinjaPortal\Shadow\Http\Controllers\Auth\RegisteredUserController;

if (! (bool) config('shadow-theme.enabled', true)) {
    return;
}

$prefix = trim((string) config('shadow-theme.routes.prefix', 'portal'), '/');
$middleware = (array) config('shadow-theme.routes.middleware', ['web']);

Route::middleware(array_merge($middleware, ['shadow.user']))
    ->prefix($prefix)
    ->as('shadow.')
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
        Route::get('/products/{slug}', [ProductsController::class, 'show'])->name('products.show');

        Route::middleware('shadow.guest')->group(function () {
            Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('auth.login');
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.login.store');

            Route::get('/register', [RegisteredUserController::class, 'create'])->name('auth.register');
            Route::post('/register', [RegisteredUserController::class, 'store'])->name('auth.register.store');

            Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('auth.password.request');
            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('auth.password.email');
            Route::get('/reset-password', [NewPasswordController::class, 'create'])->name('auth.password.reset');
            Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('auth.password.update');

            Route::get('/auth/mfa', [MfaController::class, 'challenge'])->name('auth.mfa.challenge');
            Route::post('/auth/mfa/verify', [MfaController::class, 'verifyChallenge'])->name('auth.mfa.verify');
            Route::post('/auth/mfa/resend', [MfaController::class, 'resendChallenge'])->name('auth.mfa.resend');
        });

        Route::middleware('shadow.auth')->group(function () {
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');

            Route::get('/dashboard', DashboardController::class)->name('dashboard');

            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

            Route::get('/apps', [AppsController::class, 'index'])->name('apps.index');
            Route::get('/apps/create', [AppsController::class, 'create'])->name('apps.create');
            Route::post('/apps', [AppsController::class, 'store'])->name('apps.store');
            Route::get('/apps/{appName}', [AppsController::class, 'show'])->name('apps.show');
            Route::put('/apps/{appName}', [AppsController::class, 'update'])->name('apps.update');
            Route::delete('/apps/{appName}', [AppsController::class, 'destroy'])->name('apps.destroy');
            Route::post('/apps/{appName}/approve', [AppsController::class, 'approve'])->name('apps.approve');
            Route::post('/apps/{appName}/revoke', [AppsController::class, 'revoke'])->name('apps.revoke');

            Route::post('/apps/{appName}/credentials', [CredentialsController::class, 'store'])->name('apps.credentials.store');
            Route::post('/apps/{appName}/credentials/{key}/approve', [CredentialsController::class, 'approve'])->name('apps.credentials.approve');
            Route::post('/apps/{appName}/credentials/{key}/revoke', [CredentialsController::class, 'revoke'])->name('apps.credentials.revoke');
            Route::delete('/apps/{appName}/credentials/{key}', [CredentialsController::class, 'destroy'])->name('apps.credentials.destroy');
            Route::post('/apps/{appName}/credentials/{key}/products', [CredentialsController::class, 'addProducts'])->name('apps.credentials.products.add');
            Route::delete('/apps/{appName}/credentials/{key}/products/{product}', [CredentialsController::class, 'removeProduct'])->name('apps.credentials.products.remove');
            Route::post('/apps/{appName}/credentials/{key}/products/{product}/approve', [CredentialsController::class, 'approveProduct'])->name('apps.credentials.products.approve');
            Route::post('/apps/{appName}/credentials/{key}/products/{product}/revoke', [CredentialsController::class, 'revokeProduct'])->name('apps.credentials.products.revoke');

            Route::get('/settings/mfa', [MfaController::class, 'settings'])->name('mfa.settings');
            Route::put('/settings/mfa', [MfaController::class, 'updateSettings'])->name('mfa.settings.update');
            Route::post('/settings/mfa/authenticator/start', [MfaController::class, 'beginAuthenticator'])->name('mfa.authenticator.start');
            Route::post('/settings/mfa/authenticator/confirm', [MfaController::class, 'confirmAuthenticator'])->name('mfa.authenticator.confirm');
            Route::delete('/settings/mfa/authenticator', [MfaController::class, 'disableAuthenticator'])->name('mfa.authenticator.disable');
            Route::post('/settings/mfa/email-otp/start', [MfaController::class, 'beginEmailOtp'])->name('mfa.email-otp.start');
            Route::post('/settings/mfa/email-otp/confirm', [MfaController::class, 'confirmEmailOtp'])->name('mfa.email-otp.confirm');
            Route::delete('/settings/mfa/email-otp', [MfaController::class, 'disableEmailOtp'])->name('mfa.email-otp.disable');
        });
    });
