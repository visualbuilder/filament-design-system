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
    Writes a block of raw CSS overrides to the overlay. The CSS is injected
    on every panel page via a render hook, AFTER Filament's own styles — so
    overrides win specificity ties.

    Use for component-level tweaks the tokens layer can't reach: section
    padding, sidebar background colour, header font weight, button radius
    overrides on specific component classes, etc.

    Best practice:
      1. Call list_classes first if you don't already know the targetable
         selector. Hallucinating a class is the #1 way these overrides
         silently miss.
      2. Scope every selector to fi-* (Filament) or ds-* (catalogue) classes
         to avoid leaking styles into unrelated parts of the host app.
      3. Write idempotent CSS — running the same override twice should be
         harmless. The overlay holds the LATEST css string, not a stack.

    Validation:
      - Brace count must balance.
      - Total CSS length capped at 64KB.
      - <script>, <style>, and @import are rejected.

    Pass mode="append" to add to the existing overrides instead of replacing.
    Pass dry_run=true to preview the resulting CSS without writing.

    After writing, refresh the catalogue page (or call screenshot_catalogue)
    to verify visually. Use export_theme_css when sign-off lands to graduate
    the CSS into the host's actual theme.css file.
    EOT)]
class WriteThemeOverrides extends Tool
{
    protected const MAX_BYTES = 64 * 1024;

    public function handle(Request $request): Response
    {
        $css = (string) $request->get('css', '');
        $mode = (string) $request->get('mode', 'replace');
        $dryRun = (bool) $request->get('dry_run', false);

        if (trim($css) === '') {
            return Response::error('css is required (a non-empty string).');
        }

        if (! in_array($mode, ['replace', 'append'], true)) {
            return Response::error('mode must be "replace" or "append".');
        }

        if (strlen($css) > self::MAX_BYTES) {
            return Response::error(sprintf(
                'CSS too large (%d bytes); cap is %d.',
                strlen($css),
                self::MAX_BYTES,
            ));
        }

        if ($problem = $this->validate($css)) {
            return Response::error($problem);
        }

        $overlay = Tokens::overlay();
        $existing = $overlay['theme']['css_overrides'] ?? '';

        $next = $mode === 'append' && $existing !== ''
            ? rtrim($existing) . "\n\n" . $css
            : $css;

        if (strlen($next) > self::MAX_BYTES) {
            return Response::error(sprintf(
                'Resulting CSS too large (%d bytes); cap is %d. Reduce or use mode="replace".',
                strlen($next),
                self::MAX_BYTES,
            ));
        }

        $overlay['theme'] = $overlay['theme'] ?? [];
        $overlay['theme']['css_overrides'] = $next;

        if ($dryRun) {
            return Response::json([
                'dry_run' => true,
                'mode' => $mode,
                'effective_css' => $next,
            ]);
        }

        $this->persist($overlay);

        return Response::json([
            'written' => true,
            'mode' => $mode,
            'bytes' => strlen($next),
            'overlay_path' => Tokens::overlayPath(),
            'effective_css' => $next,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'css' => $schema->string()
                ->description('The CSS to apply. Should target fi-* or ds-* selectors. Example: ".fi-section-header h3 { font-weight: 200; letter-spacing: -0.01em; }"')
                ->required(),
            'mode' => $schema->string()
                ->description('"replace" (default) swaps the entire overrides block. "append" adds to the existing overrides.')
                ->enum(['replace', 'append']),
            'dry_run' => $schema->boolean()
                ->description('If true, return the resulting CSS without writing it to disk.'),
        ];
    }

    protected function validate(string $css): ?string
    {
        $opens = substr_count($css, '{');
        $closes = substr_count($css, '}');
        if ($opens !== $closes) {
            return "Unbalanced braces: {$opens} '{' vs {$closes} '}'.";
        }

        if (preg_match('/<\s*script/i', $css)) {
            return 'CSS must not contain <script> tags.';
        }

        if (preg_match('/<\s*style/i', $css)) {
            return 'CSS must not contain <style> tags. Pass plain CSS only — the package wraps it for you.';
        }

        if (preg_match('/^\s*@import\b/m', $css)) {
            return '@import is not allowed in overrides.';
        }

        return null;
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
