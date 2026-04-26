<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Visualbuilder\FilamentDesignSystem\Theme\Tokens;

#[Description(<<<'EOT'
    Writes a partial overlay to storage/app/design-system-tokens.json.

    The overlay shape is { "tokens": {...}, "panel": {...} } — either subtree
    is optional. The shape mirrors config('design-system.*'):

      tokens — colors / typography / spacing / radius / shadow
      panel  — font / brand / colors / vite_theme / max_content_width / default_theme_mode

    Examples:
      Set primary 500:
        { "tokens": { "colors": { "primary": { "500": "#ea746b" } } } }

      Switch the panel font to Nunito:
        { "panel": { "font": { "family": "Nunito" } } }

      Both at once:
        { "tokens": { ... }, "panel": { ... } }

    By default the overlay is deep-merged with what's already there, so partial
    edits don't wipe other tokens. Pass replace=true to swap the entire overlay.

    Colour values may be hex (#rrggbb), rgb()/rgba(), hsl()/hsla(), oklch(), or
    oklab(). Other token types (sizes, weights, font names, paths) accept any
    string.

    After writing, call screenshot_catalogue to see the visual result.
    EOT)]
class WriteTokens extends Tool
{
    public function handle(Request $request): Response
    {
        $tokens = $request->get('tokens');
        $panel = $request->get('panel');
        $replace = (bool) $request->get('replace', false);
        $dryRun = (bool) $request->get('dry_run', false);

        if (! is_array($tokens) && ! is_array($panel)) {
            return Response::error('Provide at least one of `tokens` or `panel` as a non-empty object.');
        }

        if (is_array($tokens) && ($invalid = $this->findInvalidColors($tokens))) {
            return Response::error(
                "These token colour values aren't valid CSS colours: " . implode(', ', $invalid)
                . '. Use hex, rgb(), hsl(), oklch(), or oklab().',
            );
        }

        $proposed = array_filter([
            'tokens' => is_array($tokens) ? $tokens : null,
            'panel' => is_array($panel) ? $panel : null,
        ], fn ($v) => $v !== null);

        $existing = $replace ? [] : Tokens::overlay();
        $next = $this->deepMerge($existing, $proposed);

        if ($dryRun) {
            return Response::json(['dry_run' => true, 'effective_overlay' => $next]);
        }

        $this->persist($next);

        return Response::json([
            'written' => true,
            'overlay_path' => Tokens::overlayPath(),
            'effective_overlay' => $next,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'tokens' => $schema->object()
                ->description('Partial tokens tree (colors, typography, spacing, radius, shadow). Shape mirrors config(\'design-system.tokens\').'),
            'panel' => $schema->object()
                ->description('Partial panel chrome config (font, brand, vite_theme, max_content_width, default_theme_mode). Shape mirrors config(\'design-system.panel\').'),
            'replace' => $schema->boolean()
                ->description('If true, replace the entire overlay instead of deep-merging.'),
            'dry_run' => $schema->boolean()
                ->description('If true, return the resulting overlay without writing it to disk.'),
        ];
    }

    /**
     * Walk the proposed tokens tree looking for colour values under colors.*
     * and reject anything that doesn't parse as a CSS colour.
     *
     * @param  array<string, mixed>  $tokens
     * @return list<string>
     */
    protected function findInvalidColors(array $tokens): array
    {
        $invalid = [];

        $colors = $tokens['colors'] ?? null;
        if (! is_array($colors)) {
            return [];
        }

        foreach ($colors as $key => $value) {
            $local = "colors.{$key}";

            if (is_string($value)) {
                if (! Tokens::looksLikeColor($value)) {
                    $invalid[] = "{$local} = {$value}";
                }
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $shade => $hex) {
                    if (is_string($hex) && ! Tokens::looksLikeColor($hex)) {
                        $invalid[] = "{$local}.{$shade} = {$hex}";
                    }
                }
            }
        }

        return $invalid;
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overlay
     * @return array<string, mixed>
     */
    protected function deepMerge(array $base, array $overlay): array
    {
        foreach ($overlay as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key]) && $this->isAssoc($value) && $this->isAssoc($base[$key])) {
                $base[$key] = $this->deepMerge($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    protected function isAssoc(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * @param  array<string, mixed>  $overlay
     */
    protected function persist(array $overlay): void
    {
        $path = Tokens::overlayPath();
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        file_put_contents($tmp, json_encode($overlay, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        rename($tmp, $path);
    }
}
