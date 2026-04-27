<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp\Tools;

use Filament\Facades\Filament;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\URL;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Visualbuilder\FilamentDesignSystem\FilamentDesignSystemPlugin;

#[Description(<<<'EOT'
    Captures a screenshot of a catalogue page so the model can see the visual result of a token edit.

    Pages: overview | forms | layout | actions | tables | infolists | widgets

    Returns the screenshot as a base64 image. Call this after write_tokens to verify the
    visual result of a change. Capturing requires the host app to register a screenshot
    closure on the plugin — if it isn't wired up, the tool returns guidance instead of an
    image, and the model should report that to the user rather than retrying.
    EOT)]
#[IsReadOnly]
class ScreenshotCatalogue extends Tool
{
    /**
     * Page slugs accepted by the tool — kept in sync with the catalogue Pages
     * registered by FilamentDesignSystemPlugin::register().
     */
    protected const PAGES = ['index', 'forms', 'layout', 'actions', 'tables', 'infolists', 'widgets'];

    public function handle(Request $request): Response
    {
        $page = (string) $request->get('page', 'index');

        if ($page === 'overview') {
            $page = 'index';
        }

        if (! in_array($page, self::PAGES, true)) {
            return Response::error(
                "Unknown page \"{$page}\". Valid pages: " . implode(', ', self::PAGES) . '.',
            );
        }

        $plugin = $this->plugin();

        if (! $plugin || ! $plugin->hasScreenshotCapture()) {
            return Response::text($this->setupGuidance());
        }

        $url = URL::temporarySignedRoute(
            'filament-design-system.screenshot',
            now()->addMinute(),
            ['page' => $page],
        );

        try {
            $captured = ($plugin->getScreenshotCaptureCallback())($url);
        } catch (\Throwable $e) {
            // Catch host-side failures so a misconfigured capture closure
            // doesn't propagate up and kill the MCP connection.
            return Response::error(sprintf(
                'Screenshot closure threw %s: %s',
                $e::class,
                $e->getMessage(),
            ));
        }

        if ($captured === null) {
            return Response::error('Screenshot closure returned null — capture failed. Check your Playwright install (npx playwright install chromium) or the host-registered closure.');
        }

        [$base64, $mime] = $this->normalise($captured);

        if ($base64 === null) {
            return Response::error('Screenshot closure returned an unrecognised payload. Expected a base64 string or [\"image\" => base64, \"mime\" => \"image/png\"].');
        }

        // The MCP protocol supports inline image responses, but Laravel\Mcp\Response::image()
        // is not implemented in this version of laravel/mcp. As a workaround we persist the
        // PNG to a known location and return its path as text — the AI client can then read
        // the file via its native file-read tool to "see" the screenshot. When upstream
        // adds image-content support to Tool responses, swap this for Response::image().
        $extension = $mime === 'image/jpeg' ? 'jpg' : 'png';
        $directory = storage_path('app/design-system-screenshots');
        @mkdir($directory, 0755, true);
        $path = $directory . '/' . date('Ymd-His') . '-' . $page . '.' . $extension;

        if (file_put_contents($path, base64_decode($base64, true)) === false) {
            return Response::error("Failed to write screenshot to {$path}.");
        }

        return Response::text(
            "Screenshot saved to: {$path}\n\n"
            . "Read this path with your file-read tool to view the captured PNG. "
            . "Older screenshots in this directory are not auto-pruned."
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page' => $schema->string()
                ->description('Catalogue page slug to capture: overview, forms, layout, actions, tables, infolists, or widgets.'),
        ];
    }

    protected function plugin(): ?FilamentDesignSystemPlugin
    {
        $panel = Filament::getPanel('design-system', isStrict: false);

        if (! $panel) {
            return null;
        }

        $plugin = $panel->getPlugin('filament-design-system');

        return $plugin instanceof FilamentDesignSystemPlugin ? $plugin : null;
    }

    /**
     * Accept either a raw base64 string or an array shaped
     *   ['image' => '<base64>', 'mime' => 'image/png']
     * Mirrors the convention used by EmailTemplatesPlugin::screenshotCapture so
     * a host that wires both can use the same closure.
     *
     * @param  mixed  $payload
     * @return array{0: ?string, 1: ?string}
     */
    protected function normalise(mixed $payload): array
    {
        if (is_string($payload)) {
            return [$payload, null];
        }

        if (is_array($payload)) {
            $base64 = $payload['image'] ?? $payload['base64'] ?? null;
            $mime = $payload['mime'] ?? $payload['contentType'] ?? null;

            return [is_string($base64) ? $base64 : null, is_string($mime) ? $mime : null];
        }

        return [null, null];
    }

    protected function setupGuidance(): string
    {
        return <<<'EOT'
            Screenshots are unavailable: Playwright is not installed in this project's node_modules, and no custom screenshot closure has been registered on FilamentDesignSystemPlugin.

            Recommended fix — install Playwright:

                npm install --save-dev playwright
                npx playwright install chromium

            That's all most hosts need. The package will detect the install and use the bundled local-Chromium capture automatically.

            Advanced override — register a custom closure (e.g. when using a hosted screenshot service):

                FilamentDesignSystemPlugin::make()
                    ->screenshotCapture(function (string $url): ?array {
                        // return ['image' => '<base64>', 'mime' => 'image/png'] or null
                    }),

            Without a working capture, the rest of the MCP server (read_tokens, write_tokens, write_theme_overrides) still works — token edits just won't have visual confirmation.
            EOT;
    }
}
