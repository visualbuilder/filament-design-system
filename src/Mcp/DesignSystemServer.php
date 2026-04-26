<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ReadTokens;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\ScreenshotCatalogue;
use Visualbuilder\FilamentDesignSystem\Mcp\Tools\WriteTokens;

#[Name('Filament Design System')]
#[Version('0.3.0')]
#[Instructions(<<<'EOT'
    This MCP server exposes the design-system tokens of a Filament v5 application.

    Workflow:
    1. Call read_tokens to see the current effective theme — colours (with shade ramps),
       typography, spacing, radii, shadows — plus the catalogue layout (which colour
       sections the panel renders) and panel chrome config (font, logos, max width).
    2. Reason about the change requested. If the user asks for visual edits ("make
       the brand more vibrant", "match this Figma frame"), translate that into specific
       token changes.
    3. Call write_tokens with a partial tokens tree. By default this deep-merges into
       the overlay so unrelated tokens are preserved. Pass dry_run=true first if you
       want to verify the resulting state without writing.
    4. (When the screenshot tool is wired up) call screenshot_catalogue to verify the
       visual result. Iterate as needed.

    The overlay file lives at storage/app/design-system-tokens.json. It is the only
    file you write to; the underlying config/design-system.php is documentation +
    defaults and stays untouched.
    EOT)]
class DesignSystemServer extends Server
{
    protected array $tools = [
        ReadTokens::class,
        WriteTokens::class,
        ScreenshotCatalogue::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
