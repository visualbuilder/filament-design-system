<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Visualbuilder\FilamentDesignSystem\Theme\Tokens;

#[Description('Returns the resolved design-system state — the token tree (colours, typography, spacing, radius, shadow), the panel chrome (font, brand, vite_theme, max_content_width, etc.), and the catalogue layout. Both tokens and panel are editable via write_tokens. Call this first to understand the current theme before proposing edits.')]
#[IsReadOnly]
class ReadTokens extends Tool
{
    public function handle(Request $request): Response
    {
        $overlay = Tokens::overlay();

        return Response::json([
            'tokens' => Tokens::resolved(),
            'panel' => Tokens::resolvedPanel(),
            'theme' => [
                'css_overrides' => $overlay['theme']['css_overrides'] ?? '',
            ],
            'overlay_path' => Tokens::overlayPath(),
            'overlay_present' => Tokens::rawOverlay() !== null,
            'catalogue' => [
                'colour_sections' => config('design-system.catalogue.colour_sections', []),
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
