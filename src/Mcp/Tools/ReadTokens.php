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

#[Description('Returns the design-system token tree (colours, typography, spacing, radius, shadow) plus the catalogue layout. Use this to understand the current theme before proposing edits via write_tokens.')]
#[IsReadOnly]
class ReadTokens extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::json([
            'tokens' => Tokens::resolved(),
            'overlay_path' => Tokens::overlayPath(),
            'overlay_present' => Tokens::overlay() !== null,
            'catalogue' => [
                'colour_sections' => config('design-system.catalogue.colour_sections', []),
            ],
            'panel' => config('design-system.panel', []),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
