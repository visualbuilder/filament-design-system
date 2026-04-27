# Changelog

All notable changes to `visualbuilder/filament-design-system` will be documented in this file.

## Unreleased

### v0.9.0 — Welcome hero component + catalogue showcase

- **`<x-filament-design-system::welcome>` Blade component.** Renders the
  "Welcome / <name>" pattern from the designer's reference: circular
  illustration badge + light/extralight pink greeting + bolder user name.
  Self-contained styles (no @push/@once) inside a single root element so
  it satisfies Livewire's single-root-element rule.
- **Catalogue Index page now showcases the component** at the top so
  designers can iterate on the Welcome chrome via the design-system MCP.
- Props: `icon` (any blade-icons name, default `heroicon-o-hand-raised`),
  `greeting` (default 'Welcome'), `name` (default 'Design Reviewer').
  Hosts that ship their own illustration (e.g. `resources/svg/wave.svg`)
  pass it by name.

### v0.8.0 — global search demo resource

- **`DesignSystemUserResource` registered on the panel**, with
  `$shouldRegisterNavigation = false` so it doesn't add a sidebar item.
  Its only purpose is to give the panel a globally-searchable resource so
  Filament renders the topbar's global-search input — pink26 designers can
  iterate on the search-pill chrome (rounded background, typography,
  CTRL+K hint) without having to wire up a real model.
- **Resource bypasses host policy stack.** `canViewAny()` / `canView()`
  return true; `canCreate()` / `canEdit()` / `canDelete()` return false.
  This skips Gate resolution that might otherwise call methods like
  `hasRole()` on `DesignSystemUser` (which doesn't use Spatie HasRoles).
  The resource is read-only by design.
- **`DemoUserSeeder` seeds eight extra users** with varied names so global
  search has demo data to surface. Hosts re-running the seeder on upgrade
  get the new records via `updateOrCreate` (no duplicates).
- **`globalSearchFieldSuffix()` is the missing piece** for the visible
  CTRL+K hint — `globalSearchKeyBindings()` only registers the keyboard
  shortcut. Both are documented in the published panel-provider stub.

### v0.7.0 — initials avatar component + light-mode parity for catalogue + theme switching in screenshots

- **`<x-filament-design-system::avatar>` component.** Anonymous Blade component
  that renders an initials-on-rounded-bg avatar — text and CSS only, no
  external requests, CSP-safe. Deterministic hue from a hash of the seed
  (same name → same colour). Sizes: `xs|sm|md|lg|xl`.
- **Catalogue migrated off `api.dicebear.com`.** `Pages/CardTables`, `Pages/Tables`,
  and `Pages/Infolists` previously used dicebear URLs as avatar fallbacks
  via `ImageColumn::defaultImageUrl()` / `ImageEntry::state()`. Replaced
  with `ViewColumn` / `ViewEntry` rendering the new component. The catalogue's
  cover-image example also moved off dicebear's `/shapes` API to a CSS
  gradient — keeps the panel CSP-safe out of the box on locked-down
  hosts (Beanstalk, etc.).
- **Two reusable views shipped alongside the component:**
  `filament-design-system::columns.avatar` (for `ViewColumn`) and
  `filament-design-system::entries.avatar` (for `ViewEntry`). Both honour
  an `extraAttributes(['data-avatar-size' => 'lg'])` per use site.
- **`screenshot.cjs` now accepts `--theme=light|dark|system`.** Pre-seeds
  localStorage and forces the `dark` class on `<html>` so AI iteration
  loops can verify light-mode rendering without manual user-menu toggling.
  Useful when graduating a theme that needs to work in both modes.

### v0.6.0 — Playwright screenshot default + crash-safe tool

- **Playwright as the default screenshot capture.** Package now ships a Node
  CLI (`resources/scripts/screenshot.cjs`) and a PHP wrapper
  (`Screenshot\PlaywrightCapture`) that drive headless Chromium against any
  local URL — including self-signed HTTPS via `ignoreHTTPSErrors`. Hosts with
  Playwright in their `node_modules` get a working screenshot loop without
  registering a custom closure. Custom closures still take precedence when
  registered. Setup: `npm install --save-dev playwright && npx playwright
  install chromium`.
- **`screenshot_catalogue` no longer crashes the MCP server on host errors.**
  The closure invocation is wrapped in `try/catch`; thrown exceptions return
  as a tool error, and host capture services that produce nothing return
  graceful guidance instead of a stdio crash.
- **PNG saved to disk + path returned as text.** Workaround for Laravel\Mcp's
  unimplemented `Response::image()` in tool responses — writes to
  `storage/app/design-system-screenshots/<timestamp>-<page>.png` and the AI
  client reads via its native file-read tool. Will switch to inline image
  content once upstream supports it.
- **`setupGuidance()` rewritten** to recommend the Playwright install path
  as canonical, with custom-closure registration as the advanced override.
- **README** rewritten to position Playwright as the supported default.

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
