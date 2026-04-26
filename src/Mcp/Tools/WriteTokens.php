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
    Writes a partial token tree to the overlay file (storage/app/design-system-tokens.json).

    The shape mirrors design-system.tokens — e.g. {"colors": {"primary": {"500": "#5d36ff"}}}.
    By default the overlay is deep-merged with what's already there, so partial edits don't
    wipe other tokens. Pass replace=true to swap the entire overlay (rare).

    Colour values may be hex (#rrggbb), rgb()/rgba(), hsl()/hsla(), oklch(), or oklab().
    Other token types (sizes, weights, durations) accept any string.

    After writing, call screenshot_catalogue to see the visual result.
    EOT)]
class WriteTokens extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var array<string, mixed>|null $tokens */
        $tokens = $request->get('tokens');
        $replace = (bool) $request->get('replace', false);
        $dryRun = (bool) $request->get('dry_run', false);

        if (! is_array($tokens) || $tokens === []) {
            return Response::error('The `tokens` argument must be a non-empty object mirroring the tokens shape.');
        }

        if ($invalid = $this->findInvalidColors($tokens)) {
            return Response::error(
                "These values don't look like valid CSS colours: " . implode(', ', $invalid)
                . '. Use hex, rgb(), hsl(), oklch(), or oklab().',
            );
        }

        $existing = Tokens::overlay() ?? [];
        $next = $replace ? $tokens : $this->deepMerge($existing, $tokens);

        if ($dryRun) {
            return Response::json([
                'dry_run' => true,
                'effective_overlay' => $next,
            ]);
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
                ->description('Partial tokens tree to merge into the overlay. Shape mirrors design-system.tokens — colors, typography, spacing, radius, shadow.')
                ->required(),
            'replace' => $schema->boolean()
                ->description('If true, replace the entire overlay instead of deep-merging.'),
            'dry_run' => $schema->boolean()
                ->description('If true, return the resulting overlay without writing it to disk.'),
        ];
    }

    /**
     * Walk the proposed tree looking for keys that look like colour-token leaves
     * (i.e. under colors.*) and validate the values format. Non-colour branches
     * are skipped.
     *
     * @param  array<string, mixed>  $tokens
     * @return list<string>
     */
    protected function findInvalidColors(array $tokens, string $path = ''): array
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
