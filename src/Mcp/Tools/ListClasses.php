<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description(<<<'EOT'
    Returns the manifest of CSS class names actually present on the catalogue
    pages — the ground truth for what selectors are available to override
    in the installed Filament version.

    Each entry maps a class name to the catalogue pages it appears on, so you
    can scope a query to e.g. only the Forms or Tables page. Filter with the
    optional `prefix` arg (e.g. "fi-ta-" for tables) or `page` arg.

    Call this before write_theme_overrides if you're uncertain whether a class
    exists. Targeting a class that isn't in the manifest is a strong signal
    you've hallucinated the name.
    EOT)]
#[IsReadOnly]
class ListClasses extends Tool
{
    public function handle(Request $request): Response
    {
        $manifest = $this->loadManifest();

        if ($manifest === null) {
            return Response::error(
                'Class manifest not found. Run `php artisan filament-design-system:rebuild-class-manifest` to generate it.',
            );
        }

        $prefix = (string) $request->get('prefix', '');
        $page = (string) $request->get('page', '');
        $classes = $manifest['classes'];

        if ($prefix !== '') {
            $classes = array_filter(
                $classes,
                fn (array $_, string $name) => str_starts_with($name, $prefix),
                ARRAY_FILTER_USE_BOTH,
            );
        }

        if ($page !== '') {
            $classes = array_filter(
                $classes,
                fn (array $pages) => in_array($page, $pages, true),
            );
        }

        return Response::json([
            'generated_at' => $manifest['generated_at'] ?? null,
            'filament_version' => $manifest['filament_version'] ?? null,
            'pages' => $manifest['pages'] ?? [],
            'count' => count($classes),
            'classes' => $classes,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'prefix' => $schema->string()
                ->description('Optional prefix filter (e.g. "fi-ta-" for tables, "fi-fo-" for forms, "fi-section-" for section chrome).'),
            'page' => $schema->string()
                ->description('Optional page filter — restrict to classes that appear on a specific catalogue page (overview, forms, layout, actions, tables, card-tables, infolists, icons).'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function loadManifest(): ?array
    {
        $path = __DIR__ . '/../../../data/filament-class-manifest.json';

        if (! is_file($path)) {
            return null;
        }

        $body = file_get_contents($path);
        if ($body === false) {
            return null;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : null;
    }
}
