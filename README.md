# Filament Design System

A standalone design-system review panel for Filament v5, plus an AI-driven theme designer over MCP.

## What it is

Two things in one package:

1. **A Filament panel** rendering the full catalogue of v5 components — forms, layout, actions, tables (including card-style grids), infolists, widgets, icons, auth screens — against a single tokens config. Drop it into any Filament v5 application and a graphic designer can review the live look-and-feel in actual Filament chrome.
2. **An MCP server** that exposes the panel's design surface to AI clients. An agent can read the current theme, generate palettes, edit tokens, swap brand assets, write component-level CSS overrides, and verify visually via screenshots — all through a stdio MCP connection.

The panel is the AI's test canvas. The MCP server is the AI's hands.

## Why a separate panel

- **Iterate the theme without risk to production panels.** Override the tokens, see the entire component library re-render, ship when ready.
- **Review surface.** A dedicated guard, a single seeded demo user, and hard-coded demo data on every page mean the panel is safe to expose behind a basic deployment gate (DNS, basic auth, env-strip).
- **AI-friendly substrate.** Tokens and panel chrome are flat, structured config. CSS overrides go through a validated layer with a class manifest the AI can query before targeting selectors. Three editable layers, one overlay file.

## Version history

- **v0.1** — panel, catalogue pages, separate guard with demo user, custom login showing credentials.
- **v0.2** — publishable tokens config, config-driven catalogue colour sections.
- **v0.3** — MCP server with `read_tokens` / `write_tokens` / `screenshot_catalogue`. JSON overlay so the AI never touches PHP.
- **v0.3.1** — overlay extended to `{ tokens, panel }`; panel chrome (font, brand, vite_theme, etc.) editable via MCP. Reset tool added.
- **v0.4** — `generate_palette` (single hex → 11-shade ramp) and `set_brand_logo` (URL / data URI / path) tools.
- **v0.5** — `write_theme_overrides` for component-level CSS the tokens layer can't reach. `list_classes` returns the actual `fi-*` / `ds-*` class manifest (extracted from the catalogue) so the AI doesn't hallucinate selectors. `export_theme_css` produces the CSS string for paste-into-`theme.css` graduation.
- **v0.6** — Playwright as the default screenshot capture (no host closure required if `node_modules/playwright` is present). `screenshot_catalogue` wraps closure calls in try/catch so host bugs no longer crash the MCP connection. PNG saved to disk and path returned as text — works around Laravel\Mcp's unimplemented `Response::image()` for tool results.
- **v0.7 (current)** — `<x-filament-design-system::avatar>` Blade component (initials on rounded bg, deterministic hue, CSP-safe). Catalogue migrated off `api.dicebear.com` for both initials and shapes — works out of the box on hosts with strict CSP (Beanstalk, etc.). `screenshot.cjs` accepts `--theme=light|dark|system` for light-mode iteration loops.

## Installation

This package is intended to be installed as a **dev dependency** so it never ships to production:

```bash
composer require --dev visualbuilder/filament-design-system
```

Gate the panel-provider registration in `bootstrap/providers.php` so it's silently skipped when the package isn't installed (e.g. on `composer install --no-dev`):

```php
if (class_exists(\Visualbuilder\FilamentDesignSystem\FilamentDesignSystemPlugin::class)) {
    $providers[] = App\Providers\Filament\DesignSystemPanelProvider::class;
}
```

Publish the panel-provider stub:

```bash
php artisan vendor:publish --tag=filament-design-system-provider
```

This drops `app/Providers/Filament/DesignSystemPanelProvider.php` into the host app. Edit it to wire your existing theme CSS, panel colours, font, logos — see the published file's comments.

Run the migration and seed the demo user:

```bash
php artisan migrate
php artisan db:seed --class="Visualbuilder\\FilamentDesignSystem\\Database\\Seeders\\DemoUserSeeder"
```

The catalogue is then live at `/design-system` (or whatever path you configure in the panel provider). Login uses the demo credentials shown on the login screen.

## MCP server

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

Server runs over stdio. No HTTP, no auth tokens, no extra ports. Restart your Claude Code session and the tool list appears.

### Inspect the server

The official MCP Inspector visualises the server's tool surface and lets you fire calls without writing JSON-RPC by hand:

```bash
php artisan mcp:inspector design-system
```

Browse the laravel-mcp [docs](https://laravel.com/docs/mcp) for further options (web transport, OAuth, etc.).

### Tools

The server registers nine tools across three editable layers — **tokens** (CSS variables), **panel** (chrome: font, logos, vite_theme), and **theme** (raw CSS overrides). All edits land in a single overlay file at `storage/app/design-system-tokens.json`.

| Tool | Read-only | What it does |
|---|---|---|
| `read_tokens` | ✓ | Resolved tokens + panel + theme overrides + catalogue layout |
| `write_tokens` | | Validates and persists a partial `{ tokens, panel }` overlay; deep-merges by default; `dry_run` and `replace` supported |
| `generate_palette` | ✓ | Single hex → 11-shade ramp (50→950) anchored at 500 by default |
| `set_brand_logo` | | Sets `panel.brand.{logo|logo_dark|favicon}` from a URL, data URI, or existing public/ path |
| `write_theme_overrides` | | Validates and persists a CSS string to `theme.css_overrides`; injected as a `<style>` block on every panel page after Filament's own styles |
| `export_theme_css` | ✓ | Returns the current overrides as a string ready to paste into `theme.css` |
| `list_classes` | ✓ | Returns the manifest of `fi-*` and `ds-*` classes actually present on the catalogue, scoped per-page; filterable by `prefix` and `page` |
| `reset_overlay` | destructive | Reverts AI edits — `scope=all` (default) / `tokens` / `panel` / `css` |
| `screenshot_catalogue` | ✓ | Captures a named catalogue page via a host-supplied closure (returns setup guidance gracefully when not configured) |

### Editing layers

The overlay file at `storage/app/design-system-tokens.json` has three top-level keys, all optional:

```json
{
  "tokens": {
    "colors":    { "primary": { "50": "#…", "500": "#…", "950": "#…" }, … },
    "typography":{ "family": { "body": "Nunito" }, "weight": { "heading": 200 } },
    "spacing":   { … },
    "radius":    { … },
    "shadow":    { … }
  },
  "panel": {
    "vite_theme":         "resources/css/filament/admin/theme.css",
    "default_theme_mode": "dark",
    "max_content_width":  "screen-4xl",
    "font":               { "family": "Nunito", "provider": "Filament\\FontProviders\\GoogleFontProvider" },
    "brand":              { "logo": "design-system/brand/logo-…svg", "favicon": "…" },
    "colors":             { "primary": [50→950 hex map], … }
  },
  "theme": {
    "css_overrides": ".fi-section-header h3 { font-weight: 200; … }"
  }
}
```

Every key in the overlay deep-merges over the PHP config (`config/design-system.php`). The PHP config stays as documentation + defaults; the overlay is the only file the AI writes.

### Example workflows

These are the kinds of natural-language asks that map cleanly onto the tools. The agent decides which tool(s) to call.

**Change the brand colour, with a coherent ramp**

> *"Switch the primary palette to a coral around `#ea746b`. Generate the full 11-shade ramp and apply it. Show me the result."*

Agent calls: `generate_palette(hex="#ea746b")` → `write_tokens(tokens.colors.primary = <returned ramp>)` → `screenshot_catalogue(page="overview")`.

**Switch the panel font**

> *"Try the panel in Nunito instead of Roboto."*

Agent calls: `write_tokens(panel.font.family="Nunito")`. Filament loads the new Google Font on next render.

**Upload a new logo**

> *"Set the dark-mode logo to `https://example.com/logo-dark.svg`."*

Agent calls: `set_brand_logo(target="logo_dark", source="https://example.com/logo-dark.svg")`. The asset is fetched, content-hashed, saved under `public/design-system/brand/`, and `panel.brand.logo_dark` is updated.

**Component-level layout tweak**

> *"Section headers feel heavy. Find the right class, then lighten heading weight and tighten letter-spacing."*

Agent calls: `list_classes(prefix="fi-section-")` → reasons over the result → `write_theme_overrides(css=".fi-section-header h3 { font-weight: 200; letter-spacing: -0.01em; }")` → `screenshot_catalogue(page="forms")`.

**Graduate signed-off CSS into the host's theme file**

> *"Looks good. Give me the CSS so I can paste it into `theme.css`."*

Agent calls: `export_theme_css()` and returns the CSS string with a generated-at banner. You paste it into `resources/css/filament/{panel}/theme.css` and ask the agent to *"reset the css overlay layer"* — `reset_overlay(scope="css")`.

**Revert everything**

> *"Throw away all my changes."*

Agent calls: `reset_overlay()` (scope defaults to `all`). Overlay file deleted; you're back to the PHP config defaults.

## Screenshots

The `screenshot_catalogue` tool drives a headless Chromium via Playwright by default. The MCP server signs a temporary URL pointing at a bridge route that logs in the demo user and redirects to the requested catalogue page; the bundled Node CLI navigates to it and writes a PNG.

**Setup — one-time per project:**

```bash
npm install --save-dev playwright
npx playwright install chromium
```

That's all. The package detects the Playwright install at runtime and the screenshot tool starts working. No custom closure, no host service, no AWS keys.

### Override with a custom capture closure

Hosts that prefer a different mechanism (hosted screenshot service, Puppeteer, AWS Lambda, etc.) can register their own closure on the plugin. It receives the same signed URL and should return either a base64 string or `['image' => '<base64>', 'mime' => 'image/png']`:

```php
FilamentDesignSystemPlugin::make()
    ->screenshotCapture(function (string $url): ?array {
        // your capture logic here, e.g. invoking a hosted service
        return ['image' => $base64, 'mime' => 'image/png'];
    }),
```

When a custom closure is registered it takes precedence over the Playwright default. Without either, the other tools (`read_tokens`, `write_tokens`, `write_theme_overrides`, …) still work — token edits just won't have visual confirmation, and `screenshot_catalogue` returns setup guidance.

## Refreshing the class manifest

`list_classes` reads from a static manifest at `data/filament-class-manifest.json`. After upgrading `filament/filament` (or making major changes to the catalogue), refresh it:

```bash
php artisan filament-design-system:rebuild-class-manifest
```

The command auths the demo user, server-renders each catalogue page, extracts every `fi-*` and `ds-*` class, and writes the deduped per-page manifest. Initial release captures ~378 classes across 8 pages.

## Deployment guidance

The panel is intentionally permissive (login page shows the password; demo flows are no-ops). It is *not* meant to ship to production untouched. Recommended gating, in order of effort:

1. Add a dev-only DNS entry for the design-system panel.
2. Wrap the panel route in nginx basic auth.
3. Conditionally register the panel provider only outside `production` environments.
4. Install as `--dev` so production composer installs never see the package at all.

## License

GPL-2.0-or-later. Copyright © Visual Builder.
