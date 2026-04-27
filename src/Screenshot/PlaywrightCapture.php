<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Screenshot;

use Closure;
use Illuminate\Support\Facades\Process;

/**
 * Default screenshot capture for the design-system MCP server.
 *
 * Spawns the bundled Node CLI (resources/scripts/screenshot.cjs) which drives
 * headless Chromium via Playwright. Works against any local URL — including
 * self-signed HTTPS — so any host with Playwright in its node_modules gets a
 * working iteration loop without registering a custom closure.
 *
 * Hosts can override the default by registering a closure on the plugin via
 * FilamentDesignSystemPlugin::screenshotCapture(), e.g. when they prefer a
 * hosted screenshot service (AWS Lambda, Browserless, Puppeteer, etc.).
 */
class PlaywrightCapture
{
    /**
     * Returns a closure compatible with FilamentDesignSystemPlugin::screenshotCapture().
     *
     * @param  array{node_binary?: string, viewport?: string, waitMs?: int, fullPage?: bool, timeout?: int}  $options
     */
    public static function callback(array $options = []): Closure
    {
        return static function (string $url) use ($options): ?array {
            $output = tempnam(sys_get_temp_dir(), 'ds-screenshot-');

            if ($output === false) {
                return null;
            }

            $output .= '.png';

            try {
                $command = [
                    $options['node_binary'] ?? 'node',
                    static::scriptPath(),
                    $url,
                    $output,
                    $options['viewport'] ?? '1366x768',
                    (string) ($options['waitMs'] ?? 1500),
                ];

                if (($options['fullPage'] ?? true) === false) {
                    $command[] = '--no-full-page';
                }

                $result = Process::path(base_path())
                    ->timeout($options['timeout'] ?? 60)
                    ->run($command);

                if (! $result->successful() || ! is_file($output) || filesize($output) === 0) {
                    return null;
                }

                $body = @file_get_contents($output);

                return $body === false ? null : [
                    'image' => base64_encode($body),
                    'mime' => 'image/png',
                ];
            } finally {
                if (is_file($output)) {
                    @unlink($output);
                }
            }
        };
    }

    /**
     * Whether the host has Playwright installed in node_modules. Cheap to
     * call — only checks for the package directory's presence.
     */
    public static function isAvailable(): bool
    {
        return is_dir(base_path('node_modules/playwright'));
    }

    /**
     * Absolute path to the bundled Node CLI script.
     */
    public static function scriptPath(): string
    {
        return __DIR__ . '/../../resources/scripts/screenshot.cjs';
    }
}
