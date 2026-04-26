<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Visualbuilder\FilamentDesignSystem\Theme\Tokens;

#[Description(<<<'EOT'
    Removes the JSON overlay file, reverting every AI edit and restoring the
    PHP-config defaults. Use this when the user asks to undo, reset, or revert
    changes — or when starting a fresh design exploration.

    By default reset is total. Pass scope="tokens" to drop only the tokens
    subtree, or scope="panel" to drop only the panel subtree (the other
    subtree is preserved).

    No-op if no overlay exists.
    EOT)]
#[IsDestructive]
class ResetOverlay extends Tool
{
    public function handle(Request $request): Response
    {
        $scope = (string) $request->get('scope', 'all');

        if (! in_array($scope, ['all', 'tokens', 'panel'], true)) {
            return Response::error('scope must be one of: all, tokens, panel.');
        }

        $path = Tokens::overlayPath();

        if (! is_file($path)) {
            return Response::json([
                'reset' => false,
                'reason' => 'No overlay file present — nothing to reset.',
                'overlay_path' => $path,
            ]);
        }

        if ($scope === 'all') {
            unlink($path);

            return Response::json([
                'reset' => true,
                'scope' => 'all',
                'overlay_path' => $path,
            ]);
        }

        $existing = Tokens::overlay();
        $existing[$scope] = [];

        if (($existing['tokens'] ?? []) === [] && ($existing['panel'] ?? []) === []) {
            unlink($path);

            return Response::json([
                'reset' => true,
                'scope' => $scope,
                'overlay_removed' => true,
                'overlay_path' => $path,
            ]);
        }

        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        file_put_contents($tmp, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        rename($tmp, $path);

        return Response::json([
            'reset' => true,
            'scope' => $scope,
            'effective_overlay' => $existing,
            'overlay_path' => $path,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'scope' => $schema->string()
                ->description('What to reset. "all" (default) removes the overlay file entirely. "tokens" drops only the tokens subtree. "panel" drops only the panel subtree.')
                ->enum(['all', 'tokens', 'panel']),
        ];
    }
}
