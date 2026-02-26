<?php

namespace NinjaPortal\Shadow\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use NinjaPortal\Portal\Contracts\Services\SettingServiceInterface;
use NinjaPortal\Shadow\ShadowServiceProvider;

class InstallCommand extends Command
{
    protected $signature = 'shadow:install
        {--publish-config : Publish the shadow-theme config file}
        {--publish-views : Publish the shadow-theme views for customization}';

    protected $description = 'Install the Shadow Theme package and seed branding settings';

    public function handle(): int
    {
        $this->info('Installing Shadow Theme...');

        if ((bool) $this->option('publish-config')) {
            $this->call('vendor:publish', [
                '--provider' => ShadowServiceProvider::class,
                '--tag' => 'shadow-theme-config',
            ]);
        }

        if ((bool) $this->option('publish-views')) {
            $this->call('vendor:publish', [
                '--provider' => ShadowServiceProvider::class,
                '--tag' => 'shadow-theme-views',
            ]);
        }

        $this->seedBrandingSettings();

        $this->components->info('Shadow Theme install completed.');

        return self::SUCCESS;
    }

    private function seedBrandingSettings(): void
    {
        /** @var SettingServiceInterface $settings */
        $settings = $this->laravel->make(SettingServiceInterface::class);

        $table = $settings->query()->getModel()->getTable();
        if (! Schema::hasTable($table)) {
            $this->components->warn(sprintf(
                'Skipping branding settings seed because the `%s` table does not exist yet. Run portal migrations first.',
                $table
            ));

            return;
        }

        $defaults = [
            'portal.name' => [
                'value' => (string) (config('shadow-theme.branding.name') ?: config('app.name', 'NinjaPortal')),
                'type' => 'string',
            ],
            'portal.tagline' => [
                'value' => (string) config('shadow-theme.branding.tagline', ''),
                'type' => 'string',
            ],
            'portal.support_email' => [
                'value' => (string) (config('shadow-theme.branding.support_email') ?: config('mail.from.address', '')),
                'type' => 'string',
            ],
            'shadow.branding.logo_text' => [
                'value' => (string) config('shadow-theme.branding.logo_text', 'Shadow'),
                'type' => 'string',
            ],
            'branding.primary_color' => [
                'value' => (string) config('shadow-theme.theme.accent_color', '#22d3ee'),
                'type' => 'string',
            ],
            'branding.secondary_color' => [
                'value' => (string) config('shadow-theme.theme.accent_color_2', '#38bdf8'),
                'type' => 'string',
            ],
        ];

        $created = 0;

        foreach ($defaults as $key => $setting) {
            if ($settings->get($key) !== null) {
                continue;
            }

            $settings->set(
                key: $key,
                value: (string) ($setting['value'] ?? ''),
                type: (string) ($setting['type'] ?? 'string'),
            );

            $created++;
            $this->line(sprintf('  - Seeded setting: %s', $key));
        }

        $settings->loadAllSettings();

        if ($created === 0) {
            $this->components->info('Branding settings already exist. No new settings were created.');

            return;
        }

        $this->components->info(sprintf('Seeded %d branding setting(s) for Shadow Theme.', $created));
    }
}
