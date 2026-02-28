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
        $branding = (array) config('shadow-theme.branding', []);

        return [
            'name' => (string) (config('portal.name') ?? $branding['name'] ?? config('app.name', 'NinjaPortal')),
            'tagline' => (string) (config('portal.tagline') ?? $branding['tagline'] ?? ''),
            'logo_text' => (string) (config('shadow.branding.logo_text') ?? $branding['logo_text'] ?? 'Shadow'),
            'support_email' => (string) (config('portal.support_email') ?? $branding['support_email'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function palette(): array
    {
        $palette = (array) config('shadow-theme.theme', []);

        $palette['accent_color'] = (string) (config('branding.primary_color') ?? ($palette['accent_color'] ?? '#22d3ee'));
        $palette['accent_color_2'] = (string) (config('branding.secondary_color') ?? ($palette['accent_color_2'] ?? '#38bdf8'));

        return $palette;
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
