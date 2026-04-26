<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Console\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Attribute\AsCommand;
use Visualbuilder\FilamentDesignSystem\Models\DesignSystemUser;

/**
 * Re-extracts the Filament class manifest from the catalogue pages.
 *
 * Refreshes the JSON file the list_classes MCP tool reads, so the AI gets
 * accurate class names for the Filament version actually installed in the
 * host app. Run after upgrading filament/filament.
 */
#[AsCommand(
    name: 'filament-design-system:rebuild-class-manifest',
    description: 'Re-extract the Filament class manifest from the catalogue pages.',
)]
class RebuildClassManifestCommand extends Command
{
    protected const PAGES = [
        'overview' => 'index',
        'forms' => 'forms',
        'layout' => 'layout',
        'actions' => 'actions',
        'tables' => 'tables',
        'card-tables' => 'card-tables',
        'infolists' => 'infolists',
        'icons' => 'icons',
    ];

    public function handle(): int
    {
        $user = DesignSystemUser::query()->orderBy('id')->first();

        if (! $user) {
            $this->error('No design-system demo user is seeded. Run db:seed first.');

            return self::FAILURE;
        }

        Auth::guard('design_system')->login($user);

        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $classes = [];

        foreach (self::PAGES as $label => $slug) {
            $request = Request::create("http://design-system.local/design-system/{$slug}", 'GET');
            $response = $kernel->handle($request);
            $body = (string) $response->getContent();

            $this->components->task("Scanning /design-system/{$slug}", function () use ($body, $label, &$classes) {
                preg_match_all('/class="([^"]+)"/', $body, $matches);

                foreach ($matches[1] as $attr) {
                    foreach (preg_split('/\s+/', $attr) ?: [] as $cls) {
                        if (! preg_match('/^(fi-|ds-)[a-z0-9_-]+$/i', $cls)) {
                            continue;
                        }

                        $classes[$cls] = $classes[$cls] ?? [];
                        if (! in_array($label, $classes[$cls], true)) {
                            $classes[$cls][] = $label;
                        }
                    }
                }

                return true;
            });
        }

        ksort($classes);

        $manifest = [
            'generated_at' => now()->toIso8601String(),
            'filament_version' => InstalledVersions::getPrettyVersion('filament/filament'),
            'pages' => array_keys(self::PAGES),
            'classes' => $classes,
        ];

        $path = $this->manifestPath();
        @mkdir(dirname($path), 0755, true);
        file_put_contents(
            $path,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
        );

        $this->info(sprintf(
            "Wrote %d classes across %d pages to %s",
            count($classes),
            count(self::PAGES),
            $path,
        ));

        return self::SUCCESS;
    }

    public static function manifestPath(): string
    {
        return __DIR__ . '/../../../data/filament-class-manifest.json';
    }
}
