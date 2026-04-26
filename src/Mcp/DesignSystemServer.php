<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\GeneratePalette;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ReadTokens;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ResetOverlay;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ScreenshotCatalogue;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\SetBrandLogo;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\WriteTokens;

#[Name('Filament Design System')]
#[Version('0.4.0')]
#[Instructions(<<<'EOT'
    This MCP server exposes the design-system tokens AND panel chrome of a
    Filament v5 application. Both are AI-editable via a single overlay file.

    Workflow:
    1. Call read_tokens to see the resolved state — the token tree (colours,
       typography, spacing, radii, shadows), the panel chrome (font, brand,
       vite_theme, max_content_width, default_theme_mode), and the catalogue
       layout (which colour sections the panel renders).
    2. Reason about the change requested. Translate visual asks ("make the
       brand more vibrant", "switch to Nunito") into token or panel edits.
    3. For colour edits, prefer to update the full shade ramp (50→950) rather
       than a single shade. generate_palette synthesises a coherent ramp from
       a single hex; chain it into write_tokens.
    4. For brand assets (logo, dark logo, favicon) use set_brand_logo. It
       accepts a URL, data URI, or existing public/ path and updates the
       panel.brand.* slot in the overlay.
    5. Call write_tokens with a partial { tokens, panel } tree. By default
       the overlay is deep-merged so unrelated keys are preserved. Pass
       dry_run=true if you want to verify the resulting state without writing.
    6. (When the screenshot tool is wired up) call screenshot_catalogue to
       verify the visual result. Iterate as needed.
    7. Call reset_overlay to revert AI edits — total reset by default, or
       scope it to "tokens" or "panel" only.

    The overlay file lives at storage/app/design-system-tokens.json. It is
    the only file you write to; the underlying config/design-system.php is
    documentation + defaults and stays untouched.
    EOT)]
class DesignSystemServer extends Server
{
    protected array $tools = [
        ReadTokens::class,
        WriteTokens::class,
        GeneratePalette::class,
        SetBrandLogo::class,
        ResetOverlay::class,
        ScreenshotCatalogue::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
