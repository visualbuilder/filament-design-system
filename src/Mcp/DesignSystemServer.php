<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ExportThemeCss;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\GeneratePalette;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ListClasses;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ReadTokens;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ResetOverlay;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ScreenshotCatalogue;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\SetBrandLogo;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\WriteThemeOverrides;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\WriteTokens;

#[Name('Filament Design System')]
#[Version('0.5.0')]
#[Instructions(<<<'EOT'
    This MCP server exposes the design surface of a Filament v5 application:
    tokens (CSS variables), panel chrome (font, brand, vite_theme, etc.), and
    a theme-overrides layer for component-level CSS that tokens can't reach.
    All AI edits land in a single overlay file.

    Workflow:
    1. Call read_tokens to see the resolved state — tokens, panel, theme CSS
       overrides, plus the catalogue layout.
    2. Reason about the change. Translate visual asks ("more vibrant",
       "switch to Nunito", "tighter section padding") into the right layer.
    3. Pick the right tool:
       - Brand colours / shade ramps → generate_palette + write_tokens
       - Single token edits (typography, spacing, radii, panel font) → write_tokens
       - Brand logos / favicon → set_brand_logo
       - Component-level CSS the tokens can't reach (padding, weight on
         specific Filament classes, layout tweaks) → write_theme_overrides.
         Call list_classes first if you don't already know the targetable
         selector — hallucinating a class name is the most common failure.
    4. (When the screenshot tool is wired up) call screenshot_catalogue to
       verify the visual result. Iterate.
    5. Call reset_overlay to revert AI edits — total reset by default, or
       scope to "tokens", "panel", or "css".
    6. When the design is signed off, call export_theme_css to retrieve the
       CSS string the dev should paste into resources/css/filament/{panel}/
       theme.css. Then reset_overlay scope=css to clear the overlay layer.

    The overlay file lives at storage/app/design-system-tokens.json — the
    only file you write to. The PHP config (config/design-system.php) is
    documentation + defaults and stays untouched.
    EOT)]
class DesignSystemServer extends Server
{
    protected array $tools = [
        ReadTokens::class,
        WriteTokens::class,
        GeneratePalette::class,
        SetBrandLogo::class,
        WriteThemeOverrides::class,
        ExportThemeCss::class,
        ListClasses::class,
        ResetOverlay::class,
        ScreenshotCatalogue::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
