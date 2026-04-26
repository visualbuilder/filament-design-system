<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use RuntimeException;
use Visualbuilder\FilamentDesignSystem\Theme\Tokens;

#[Description(<<<'EOT'
    Sets a brand asset on the panel — the light logo, dark-mode logo, or favicon.

    Source can be:
      - A URL (https://... or http://...) — fetched server-side and saved under
        public/design-system/brand/.
      - A data URI (data:image/png;base64,... or data:image/svg+xml;base64,...) —
        decoded and saved under public/design-system/brand/.
      - A relative path to an existing file under public/ — verified and stored
        on the panel.brand key as-is. Useful when the host already serves the asset.

    Persists the resulting path into panel.brand.{target} in the overlay so the
    panel chrome picks it up on the next render. Allowed extensions: svg, png,
    jpg, jpeg, webp, ico.
    EOT)]
class SetBrandLogo extends Tool
{
    protected const TARGETS = ['logo', 'logo_dark', 'favicon'];

    protected const ALLOWED_EXTENSIONS = ['svg', 'png', 'jpg', 'jpeg', 'webp', 'ico'];

    protected const MAX_BYTES = 2 * 1024 * 1024;

    public function handle(Request $request): Response
    {
        $target = (string) $request->get('target', '');
        $source = (string) $request->get('source', '');

        if (! in_array($target, self::TARGETS, true)) {
            return Response::error(
                'target must be one of: ' . implode(', ', self::TARGETS) . '.',
            );
        }

        if (trim($source) === '') {
            return Response::error('source is required (URL, data URI, or relative path).');
        }

        try {
            $relativePath = $this->resolveSource($source, $target);
        } catch (RuntimeException $e) {
            return Response::error('Failed to set brand asset: ' . $e->getMessage());
        }

        $overlay = Tokens::overlay();
        $overlay['panel'] = $overlay['panel'] ?? [];
        $overlay['panel']['brand'] = $overlay['panel']['brand'] ?? [];
        $overlay['panel']['brand'][$target] = $relativePath;

        $this->persist($overlay);

        return Response::json([
            'target' => $target,
            'panel_value' => $relativePath,
            'overlay_path' => Tokens::overlayPath(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'target' => $schema->string()
                ->description('Which brand slot to update: "logo" (light mode), "logo_dark" (dark mode), or "favicon".')
                ->enum(self::TARGETS)
                ->required(),
            'source' => $schema->string()
                ->description('Where the asset comes from. Accepts: an https:// URL, a data:image/...;base64,... URI, or a relative path under public/ that already exists.')
                ->required(),
        ];
    }

    protected function resolveSource(string $source, string $target): string
    {
        if (str_starts_with($source, 'data:image/')) {
            return $this->saveDataUri($source, $target);
        }

        if (preg_match('/^https?:\/\//i', $source)) {
            return $this->saveUrl($source, $target);
        }

        $publicPath = public_path(ltrim($source, '/'));
        if (! is_file($publicPath)) {
            throw new RuntimeException("File not found at public/{$source}");
        }

        return ltrim($source, '/');
    }

    protected function saveUrl(string $url, string $target): string
    {
        $body = $this->fetch($url);

        $extension = $this->guessExtension(parse_url($url, PHP_URL_PATH) ?? '', $body);

        return $this->writeAsset($body, $extension, $target);
    }

    protected function saveDataUri(string $uri, string $target): string
    {
        if (! preg_match('/^data:image\/([a-z+\-]+);base64,(.+)$/i', $uri, $m)) {
            throw new RuntimeException('Invalid data URI; expected data:image/<type>;base64,<data>.');
        }

        $extension = $this->dataUriMimeToExtension(strtolower($m[1]));
        $body = base64_decode($m[2], true);

        if ($body === false) {
            throw new RuntimeException('Invalid base64 in data URI.');
        }

        return $this->writeAsset($body, $extension, $target);
    }

    protected function fetch(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'follow_location' => 1,
                'max_redirects' => 3,
                'header' => "User-Agent: filament-design-system/0.4 set_brand_logo\r\n",
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            throw new RuntimeException("Failed to fetch {$url}");
        }

        if (strlen($body) > self::MAX_BYTES) {
            throw new RuntimeException(sprintf(
                'Asset is too large (%d bytes); max %d bytes.',
                strlen($body),
                self::MAX_BYTES,
            ));
        }

        return $body;
    }

    protected function guessExtension(string $path, string $body): string
    {
        $fromPath = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($fromPath, self::ALLOWED_EXTENSIONS, true)) {
            return $fromPath;
        }

        // Sniff a couple of magic bytes for common formats.
        $head = substr($body, 0, 8);
        return match (true) {
            str_starts_with($head, "\x89PNG") => 'png',
            str_starts_with($body, '<svg') || str_contains(substr($body, 0, 200), '<svg') => 'svg',
            str_starts_with($head, "\xff\xd8\xff") => 'jpg',
            str_starts_with($body, 'RIFF') && str_contains(substr($body, 0, 12), 'WEBP') => 'webp',
            default => 'png',
        };
    }

    protected function dataUriMimeToExtension(string $mime): string
    {
        $extension = match ($mime) {
            'svg+xml' => 'svg',
            'jpeg' => 'jpg',
            'x-icon', 'vnd.microsoft.icon' => 'ico',
            default => $mime,
        };

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException("Unsupported image type: {$mime}.");
        }

        return $extension;
    }

    protected function writeAsset(string $body, string $extension, string $target): string
    {
        if (strlen($body) > self::MAX_BYTES) {
            throw new RuntimeException(sprintf(
                'Asset is too large (%d bytes); max %d bytes.',
                strlen($body),
                self::MAX_BYTES,
            ));
        }

        $relative = "design-system/brand/{$target}-" . substr(md5($body), 0, 8) . ".{$extension}";
        $absolute = public_path($relative);

        $dir = dirname($absolute);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($absolute, $body);

        return $relative;
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
