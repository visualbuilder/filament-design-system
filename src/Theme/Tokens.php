<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Theme;

/**
 * Flattens the tokens config into CSS custom properties.
 *
 * The output is concatenated into a single :root { ... } rule and injected via
 * a render hook. Naming follows --{category}-{key}-{nested-keys} so every
 * token is addressable from CSS without ambiguity.
 *
 * Tokens are merged from two sources, with the second taking priority:
 *   1. config('design-system.tokens') — defaults + host overrides in PHP
 *   2. storage/app/design-system-tokens.json — the AI-writable overlay
 *
 * The JSON layer is what the MCP write_tokens tool edits. Keeping it separate
 * from the PHP config means the AI never has to deal with PHP syntax or the
 * `config()` lookups in the published file.
 */
class Tokens
{
    public static function toCss(): string
    {
        $variables = collect(static::flatten(static::resolved()))
            ->map(fn (string $value, string $key) => "    {$key}: {$value};")
            ->implode("\n");

        return ":root {\n{$variables}\n}\n";
    }

    public static function flat(): array
    {
        return static::flatten(static::resolved());
    }

    /**
     * Effective tokens — config values with the JSON overlay deep-merged on top.
     *
     * @return array<string, mixed>
     */
    public static function resolved(): array
    {
        $base = config('design-system.tokens', []);
        $overlay = static::overlay();

        return $overlay
            ? static::deepMerge($base, $overlay)
            : $base;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function overlay(): ?array
    {
        $path = static::overlayPath();

        if (! is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    public static function overlayPath(): string
    {
        return storage_path('app/design-system-tokens.json');
    }

    /**
     * Recursive merge that preserves keys from $base when they're missing in
     * $overlay, replaces leaves where the overlay sets them, and merges nested
     * arrays. Distinct from array_replace_recursive in that lists (sequential
     * arrays) are replaced wholesale rather than zipped — which is what you
     * want for a colour ramp like [50 => '#…', …, 950 => '#…'].
     *
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overlay
     * @return array<string, mixed>
     */
    protected static function deepMerge(array $base, array $overlay): array
    {
        foreach ($overlay as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key]) && static::isAssoc($value) && static::isAssoc($base[$key])) {
                $base[$key] = static::deepMerge($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    protected static function isAssoc(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * Whether a token value reads as a CSS colour. Matches hex codes and the
     * functional notations CSS supports today (rgb/rgba/hsl/hsla/oklch/oklab/
     * color()). Used by the catalogue to decide whether to render a swatch.
     *
     * Intentionally strict on the start anchor so values like a box-shadow
     * (which embed rgb() inside a longer string) are not treated as colours.
     */
    public static function looksLikeColor(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return (bool) preg_match('/^(#[0-9a-f]{3,8}|rgba?\(|hsla?\(|oklch\(|oklab\(|color\()/i', $value);
    }

    /**
     * @return array<string, string>
     */
    protected static function flatten(array $tokens, string $prefix = '--'): array
    {
        $result = [];

        foreach ($tokens as $key => $value) {
            $name = $prefix . static::segment($prefix, $key);

            if (is_array($value)) {
                $result += static::flatten($value, $name . '-');
                continue;
            }

            $result[$name] = (string) $value;
        }

        return $result;
    }

    protected static function segment(string $prefix, string|int $key): string
    {
        if ($prefix === '--') {
            return match ((string) $key) {
                'colors' => 'color',
                'typography' => 'type',
                'spacing' => 'space',
                'radius' => 'radius',
                'shadow' => 'shadow',
                default => (string) $key,
            };
        }

        return (string) $key;
    }
}
