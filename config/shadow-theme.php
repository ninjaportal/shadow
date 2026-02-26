<?php

return [
    'enabled' => env('SHADOW_THEME_ENABLED', true),

    'routes' => [
        'prefix' => env('SHADOW_THEME_ROUTE_PREFIX', ''),
        'middleware' => ['web'],
    ],

    'auth' => [
        'session_key' => env('SHADOW_THEME_SESSION_KEY', 'shadow_theme.user_id'),
        'mfa_session_key' => env('SHADOW_THEME_MFA_SESSION_KEY', 'shadow_theme.mfa_login'),
        'context' => 'consumer',
        'password_broker' => env('SHADOW_THEME_PASSWORD_BROKER', 'users'),
        'redirect_after_login' => env('SHADOW_THEME_REDIRECT_AFTER_LOGIN', ''),
        'ui' => [
            'side_image_url' => env(
                'SHADOW_THEME_AUTH_SIDE_IMAGE_URL',
                'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?auto=format&fit=crop&w=1600&q=80'
            ),
        ],
    ],

    'branding' => [
        'name' => env('SHADOW_THEME_BRAND_NAME', env('APP_NAME', 'NinjaPortal')),
        'tagline' => env('SHADOW_THEME_BRAND_TAGLINE', 'A modern developer portal experience for your APIs.'),
        'logo_text' => env('SHADOW_THEME_LOGO_TEXT', 'Shadow'),
        'support_email' => env('SHADOW_THEME_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS')),
    ],

    'theme' => [
        'default_mode' => env('SHADOW_THEME_DEFAULT_MODE', 'dark'),
        'light' => env('SHADOW_THEME_LIGHT_DAISY_THEME', 'corporate'),
        'dark' => env('SHADOW_THEME_DARK_DAISY_THEME', 'night'),
        'accent_color' => env('SHADOW_THEME_ACCENT_COLOR', '#22d3ee'),
        'accent_color_2' => env('SHADOW_THEME_ACCENT_COLOR_2', '#38bdf8'),
        'hero_glow' => env('SHADOW_THEME_HERO_GLOW', 'rgba(34, 211, 238, 0.25)'),
    ],

    'localization' => [
        'force_locale' => env('SHADOW_THEME_LOCALE', ''),
        'rtl_locales' => array_values(array_filter(array_map(
            static fn (string $locale) => trim(strtolower($locale)),
            explode(',', (string) env('SHADOW_THEME_RTL_LOCALES', 'ar,fa,he,ur'))
        ))),
    ],

    'features' => [
        'registration' => (bool) env('SHADOW_THEME_REGISTRATION_ENABLED', true),
        'password_reset' => (bool) env('SHADOW_THEME_PASSWORD_RESET_ENABLED', true),
        'mfa' => [
            'ui_enabled' => (bool) env('SHADOW_THEME_MFA_UI_ENABLED', true),
        ],
    ],

    'catalog' => [
        'per_page' => (int) env('SHADOW_THEME_PRODUCTS_PER_PAGE', 12),
        'featured_limit' => (int) env('SHADOW_THEME_FEATURED_PRODUCTS_LIMIT', 6),
    ],

    'apps' => [
        'per_page' => (int) env('SHADOW_THEME_APPS_PER_PAGE', 15),
    ],
];
