# Filament Design System

A standalone design-system review panel for Filament v5 — and the substrate for an AI-friendly theme designer.

## What it is

A Filament panel that renders the full catalogue of Filament v5 components (forms, layout, actions, tables, infolists, widgets, auth, account screens) against a single tokens config. Drop it into any Filament v5 application and a graphic designer can review the live look-and-feel in actual Filament chrome — sidebar, topbar, dark/light mode, the lot.

## Why a separate panel

- **Iterate the theme without risk to production panels.** Override the tokens, see the entire component library re-render, ship when ready.
- **Public review surface.** A dedicated guard, a single seeded demo user, and hard-coded demo data on every page mean the panel is safe to expose behind a basic deployment gate (DNS, basic auth, env-strip).
- **AI-friendly substrate.** Tokens are a flat, structured config — exactly the shape an LLM can edit reliably. The catalogue is the LLM's test surface.

## Roadmap

- **v0.1:** panel, catalogue pages, separate guard with demo user, custom login showing credentials.
- **v0.2:** publishable tokens config, config-driven catalogue colour sections, single source of truth.
- **v0.3 (current):** MCP server with `read_tokens`, `write_tokens`, `screenshot_catalogue`. JSON tokens overlay so an LLM never has to touch PHP.
- **v0.4:** prompt library shipped with the server (re-skin from image, raise contrast, generate complementary palette).

## Installation

This package is intended to be installed as a **dev dependency** so it never ships to production:

```bash
composer require --dev visualbuilder/filament-design-system
```

Then gate the panel-provider registration in `bootstrap/providers.php` so it's silently skipped when the package isn't installed (e.g. on `composer install --no-dev`):

```php
if (class_exists(\Visualbuilder\FilamentDesignSystem\FilamentDesignSystemPlugin::class)) {
    $providers[] = App\Providers\Filament\DesignSystemPanelProvider::class;
}
```

Publish the panel-provider stub:

```bash
php artisan vendor:publish --tag=filament-design-system-provider
```

This drops `app/Providers/Filament/DesignSystemPanelProvider.php` into the host app. Register it in `bootstrap/providers.php`.

Run the migration and seed the demo user:

```bash
php artisan migrate
php artisan db:seed --class="Visualbuilder\\FilamentDesignSystem\\Database\\Seeders\\DemoUserSeeder"
```

## MCP server (v0.3)

The package ships a Laravel MCP server that lets an AI client read and write the design tokens, then verify the visual result via screenshots. Two tools are always available; the third (`screenshot_catalogue`) requires a one-line closure registration in your panel provider.

### Tools

- **`read_tokens`** — returns the resolved token tree, the catalogue layout, and panel chrome config. Read-only.
- **`write_tokens`** — accepts a partial token tree, validates colour values, and writes to `storage/app/design-system-tokens.json` (the AI overlay). Deep-merges by default; supports `dry_run` for proposing without persisting.
- **`screenshot_catalogue`** — captures a named catalogue page (`overview` / `forms` / `layout` / `actions` / `tables` / `infolists` / `widgets`) and returns base64. Returns setup guidance instead of an image if no screenshot closure is registered.

### Wire it up in Claude Code

Add to your project's `.mcp.json`:

```json
{
  "mcpServers": {
    "design-system": {
      "command": "php",
      "args": ["artisan", "mcp:start", "design-system"]
    }
  }
}
```

The server runs over stdio. No HTTP, no auth, no extra ports.

### Optional: register a screenshot capture closure

Pass a closure to `screenshotCapture()` on the plugin in `DesignSystemPanelProvider`. The closure receives a one-time signed URL that points at a bridge route which logs in the demo user and redirects to the requested catalogue page — your screenshot tool just has to fetch the URL with a headless browser (Lambda Chrome, Playwright, Browsershot, etc.) that follows redirects with cookies.

```php
FilamentDesignSystemPlugin::make()
    ->screenshotCapture(function (string $url): ?array {
        $captured = app(\App\Services\ScreenshotService::class)->capture($url, [
            'viewport' => ['width' => 1280, 'height' => 800],
            'fullPage' => true,
        ]);

        $body = @file_get_contents($captured['url'] ?? '');

        return $body === false ? null : [
            'image' => base64_encode($body),
            'mime' => 'image/png',
        ];
    }),
```

The closure should return either a base64 string or an array `['image' => '<base64>', 'mime' => 'image/png']`. Without it, `read_tokens` and `write_tokens` still work — token edits just won't have visual confirmation.

### How tokens flow

1. `config/design-system.php` is the *defaults*: shipped with the package, overridable when a consumer publishes the config.
2. `storage/app/design-system-tokens.json` is the *overlay*: the AI's writable surface. Keys deep-merge over the config at boot.
3. The catalogue and the panel's CSS variables both render from the merged tree, so any edit cascades.

The AI never has to write PHP. The overlay is JSON only.

## Deployment guidance

The panel is intentionally permissive (login page shows the password; demo flows are no-ops). It is *not* meant to ship to production untouched. Recommended gating, in order of effort:

1. Add a dev-only DNS entry for the design-system panel.
2. Wrap the panel route in nginx basic auth.
3. Conditionally register the panel provider only outside `production` environments.

## License

GPL-2.0-or-later. Copyright © Visual Builder.
