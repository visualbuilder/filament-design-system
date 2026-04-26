# Changelog

All notable changes to `visualbuilder/filament-design-system` will be documented in this file.

## Unreleased

### Added (v0.3 — MCP server)
- JSON tokens overlay at `storage/app/design-system-tokens.json` — the AI-writable surface, deep-merged over the PHP config at boot.
- Laravel MCP server `design-system` registered via `Mcp::local()`.
- `read_tokens` tool: returns the resolved token tree + catalogue layout + panel config.
- `write_tokens` tool: validates and persists a partial tokens tree to the overlay; supports `dry_run` and `replace`.
- `screenshot_catalogue` tool: captures a catalogue page via a host-supplied closure (returns setup guidance gracefully when not configured).
- Bridge route `_filament-design-system/screenshot/{page}` (signed, logs demo user in, redirects to catalogue).
- `FilamentDesignSystemPlugin::screenshotCapture(Closure)` setter mirroring the email-templates convention.

### Earlier (v0.1 / v0.2)
- Package skeleton, service provider, plugin class.
- `DesignSystemUser` model + migration + `DemoUserSeeder`.
- Tokens config + `Theme\Tokens` CSS-variable emitter.
- Catalogue pages: Index / Forms / Layout / Actions / Tables / Infolists / Widgets.
- Config-driven colour sections, panel chrome (vite theme, colors, font, brand, max-width).
- Custom `Auth\Login` with credentials callout via render hook.
