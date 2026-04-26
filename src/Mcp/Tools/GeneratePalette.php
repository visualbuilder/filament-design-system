<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use InvalidArgumentException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Visualbuilder\FilamentDesignSystem\Theme\PaletteGenerator;

#[Description(<<<'EOT'
    Generates an 11-shade colour ramp from a single hex seed.

    Output keys: 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950 — the
    shape every palette in design-system.tokens.colors uses. The seed sits at
    the chosen anchor shade (default 500); the rest are computed by setting
    target lightness on a Tailwind-ish curve and gently rolling off saturation
    at the pale and inky ends.

    Read-only — call this to design a ramp, then pass the result into
    write_tokens to apply it. Typical chain:

      generate_palette(hex="#ea746b")
      → returns { "50": "...", ..., "950": "..." }
      → write_tokens(tokens={ "colors": { "primary": <that map> } })

    Anchor at a shade other than 500 if the user gave you a colour they want
    at a non-mid-tone position (e.g. anchor="600" for a darker brand value).
    EOT)]
#[IsReadOnly]
class GeneratePalette extends Tool
{
    public function handle(Request $request): Response
    {
        $hex = (string) $request->get('hex', '');
        $anchor = (string) $request->get('anchor', '500');

        if (trim($hex) === '') {
            return Response::error('hex is required (#rrggbb or #rgb).');
        }

        try {
            $palette = PaletteGenerator::generate($hex, $anchor);
        } catch (InvalidArgumentException $e) {
            return Response::error($e->getMessage());
        }

        return Response::json([
            'hex' => $hex,
            'anchor' => $anchor,
            'palette' => $palette,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'hex' => $schema->string()
                ->description('The seed colour as a hex string (#rrggbb or #rgb).')
                ->required(),
            'anchor' => $schema->string()
                ->description('Which shade key the seed represents. Default "500". Allowed: 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950.'),
        ];
    }
}
