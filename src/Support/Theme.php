<?php

namespace NinjaPortal\Shadow\Support;

class Theme
{
    public function routePrefix(): string
    {
        return trim((string) config('shadow-theme.routes.prefix', 'portal'), '/');
    }

    public function basePath(): string
    {
        $prefix = $this->routePrefix();

        return $prefix === '' ? '/' : '/'.$prefix;
    }

    public function path(string $path = ''): string
    {
        $path = trim($path, '/');
        $base = rtrim($this->basePath(), '/');

        if ($path === '') {
            return $base === '' ? '/' : $base;
        }

        return ($base === '' ? '' : $base).'/'.$path;
    }

    public function dashboardPath(): string
    {
        $configured = trim((string) config('shadow-theme.auth.redirect_after_login', ''), '/');
        if ($configured !== '') {
            return str_starts_with($configured, '/') ? $configured : '/'.$configured;
        }

        return $this->path('dashboard');
    }

    /**
     * @return array<string, mixed>
     */
    public function branding(): array
    {
        return (array) config('shadow-theme.branding', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function palette(): array
    {
        return (array) config('shadow-theme.theme', []);
    }

    public function locale(): string
    {
        $forced = trim((string) config('shadow-theme.localization.force_locale', ''));
        if ($forced !== '') {
            return $forced;
        }

        return (string) app()->getLocale();
    }

    public function direction(): string
    {
        return $this->isRtl() ? 'rtl' : 'ltr';
    }

    public function isRtl(): bool
    {
        $locale = strtolower(str_replace('_', '-', $this->locale()));
        $baseLocale = explode('-', $locale)[0] ?? $locale;
        $rtlLocales = collect((array) config('shadow-theme.localization.rtl_locales', ['ar', 'fa', 'he', 'ur']))
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter()
            ->values()
            ->all();

        return in_array($baseLocale, $rtlLocales, true) || in_array($locale, $rtlLocales, true);
    }
}
