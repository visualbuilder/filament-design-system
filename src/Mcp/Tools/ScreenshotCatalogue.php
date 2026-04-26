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

        $captured = ($plugin->getScreenshotCaptureCallback())($url);

        if ($captured === null) {
            return Response::error('Screenshot closure returned null — the host capture service did not produce an image.');
        }

        [$base64, $mime] = $this->normalise($captured);

        if ($base64 === null) {
            return Response::error('Screenshot closure returned an unrecognised payload. Expected a base64 string or [\"image\" => base64, \"mime\" => \"image/png\"].');
        }

        return Response::make([
            Response::image()->base64($base64, $mime ?? 'image/png'),
        ]);
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
            No screenshot closure is registered on FilamentDesignSystemPlugin.

            To enable visual feedback, register a closure on the plugin in your DesignSystemPanelProvider that takes a URL and returns either a base64 image string or an array of the form ["image" => "<base64>", "mime" => "image/png"].

            Example using a hypothetical capture service:

                FilamentDesignSystemPlugin::make()
                    ->screenshotCapture(function (string $url): ?array {
                        return app(\App\Services\ScreenshotService::class)->capture($url, [
                            'viewport' => ['width' => 1280, 'height' => 800],
                            'fullPage' => true,
                        ]);
                    }),

            Without a closure, the rest of the MCP server (read_tokens, write_tokens) still works — token edits just won't have visual confirmation.
            EOT;
    }
}
