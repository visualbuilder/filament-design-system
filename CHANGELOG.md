# Changelog

All notable changes to `visualbuilder/filament-design-system` will be documented in this file.

## Unreleased

### v0.5.0 — theme-overrides layer

- **`write_theme_overrides` tool** — accepts a CSS string, validates (balanced
  braces, no `<script>` / `<style>` / `@import`, 64KB cap), persists to
  `overlay.theme.css_overrides`. Injected on every panel page via a render
  hook AFTER Filament's own styles so overrides win specificity ties.
  Supports `mode="append"` and `dry_run=true`.
- **`export_theme_css` tool** — returns the current overrides as a string
  ready to paste into `resources/css/filament/{panel}/theme.css`. The
  graduation flow: AI iterates via overrides; once signed off, dev pastes
  to the canonical theme file and resets the overlay layer.
- **`list_classes` tool** — returns the manifest of `fi-*` and `ds-*` class
  names actually present on the catalogue pages, scoped per-page. The AI
  calls this before writing overrides to avoid hallucinating selector names.
  Filterable via `prefix` and `page` args.
- **`filament-design-system:rebuild-class-manifest` artisan command** —
  re-extracts the class manifest from the catalogue. Run after upgrading
  `filament/filament`.
- **`reset_overlay scope=css`** added to clear just the CSS overrides layer.
- `read_tokens` returns `theme.css_overrides` alongside `tokens` and `panel`
  so the AI sees the full editable surface in one call.
- Server bumped to 0.5.0; instructions updated to teach the new layer and
  the read → write → screenshot → graduate cycle.

### v0.4.0

- **`generate_palette` tool** — synthesises an 11-shade ramp (50→950) from a
  single hex seed. Anchored at 500 by default; pass `anchor` to position the
  seed elsewhere on the curve. Read-only; chain into `write_tokens` to apply.
  Backed by a Tailwind-ish HSL lightness curve with saturation roll-off at
  the pale and inky ends.
- **`set_brand_logo` tool** — sets `panel.brand.{logo|logo_dark|favicon}` from
  a URL (fetched server-side), data URI (decoded), or existing public/ path.
  Fetched assets are saved under `public/design-system/brand/` with a
  content-hash suffix. Allowed extensions: svg, png, jpg, jpeg, webp, ico.
  2MB cap on fetched/decoded payloads.
- Server bumped to 0.4.0; instructions reworked to teach the chained
  generate_palette → write_tokens flow and the new brand-asset workflow.

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
