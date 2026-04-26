# Changelog

All notable changes to `visualbuilder/filament-design-system` will be documented in this file.

## Unreleased

### v0.3.1

- **Panel chrome editable via MCP.** Overlay JSON shape extended to
  `{ "tokens": {...}, "panel": {...} }`. Either subtree optional. Legacy v0.3.0
  files (just the tokens tree at top level) are auto-detected and read as
  `{ "tokens": <legacy> }` — no migration required.
- `WriteTokens` accepts a `panel` argument alongside `tokens`. Edits to
  `panel.font`, `panel.brand`, `panel.vite_theme`, `panel.max_content_width`,
  `panel.default_theme_mode` flow through to the running panel.
- New **`reset_overlay`** tool — revert all AI edits, or scope to `"tokens"`
  or `"panel"` only.
- `ReadTokens` returns both `tokens` and `panel` resolved layers so the AI
  can see everything it can edit.
- `Tokens::resolvedPanel()` added; plugin's `applyPanelConfig()` now reads
  through it. The Overview page's typography header reads the live panel
  font name from the same resolver.

### Added (v0.3.0 — MCP server)
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
