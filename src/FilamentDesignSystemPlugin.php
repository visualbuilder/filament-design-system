<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Enums\ThemeMode;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Visualbuilder\FilamentDesignSystem\Pages\Actions;
use Visualbuilder\FilamentDesignSystem\Screenshot\PlaywrightCapture;
use Visualbuilder\FilamentDesignSystem\Theme\Tokens;
use Visualbuilder\FilamentDesignSystem\Pages\CardTables;
use Visualbuilder\FilamentDesignSystem\Pages\Forms;
use Visualbuilder\FilamentDesignSystem\Pages\Icons;
use Visualbuilder\FilamentDesignSystem\Pages\Index;
use Visualbuilder\FilamentDesignSystem\Pages\Infolists;
use Visualbuilder\FilamentDesignSystem\Pages\Layout;
use Visualbuilder\FilamentDesignSystem\Pages\Tables;
use Visualbuilder\FilamentDesignSystem\Pages\Widgets;
use Visualbuilder\FilamentDesignSystem\Widgets\BarChartDemoWidget;
use Visualbuilder\FilamentDesignSystem\Widgets\DoughnutChartDemoWidget;
use Visualbuilder\FilamentDesignSystem\Widgets\LineChartDemoWidget;
use Visualbuilder\FilamentDesignSystem\Widgets\StatsDemoWidget;

class FilamentDesignSystemPlugin implements Plugin
{
    use EvaluatesClosures;

    protected string|Closure|null $navigationGroup = null;

    protected ?Closure $screenshotCaptureCallback = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'filament-design-system';
    }

    /**
     * Override the default Playwright-based screenshot capture used by the
     * screenshot_catalogue MCP tool. The callback receives a fully-qualified
     * URL to a catalogue page and should return either a base64-encoded image
     * string or an array of the form
     *   ['image' => '<base64>', 'mime' => 'image/png']
     * Return null if capture failed.
     *
     * Most hosts won't need this — the package ships with a Playwright-driven
     * default that runs locally and works against self-signed HTTPS. Register
     * a custom closure when you want to use a different capture mechanism
     * (hosted service, Puppeteer, AWS Lambda, etc.).
     */
    public function screenshotCapture(Closure $callback): static
    {
        $this->screenshotCaptureCallback = $callback;

        return $this;
    }

    /**
     * Returns the active screenshot capture closure: the host-registered one
     * if set, otherwise the Playwright default if available, otherwise null.
     */
    public function getScreenshotCaptureCallback(): ?Closure
    {
        if ($this->screenshotCaptureCallback !== null) {
            return $this->screenshotCaptureCallback;
        }

        if (PlaywrightCapture::isAvailable()) {
            return PlaywrightCapture::callback();
        }

        return null;
    }

    public function hasScreenshotCapture(): bool
    {
        return $this->screenshotCaptureCallback !== null
            || PlaywrightCapture::isAvailable();
    }

    public function navigationGroup(string|Closure|null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->evaluate($this->navigationGroup);
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            Index::class,
            Forms::class,
            Layout::class,
            Actions::class,
            Tables::class,
            CardTables::class,
            Infolists::class,
            Icons::class,
            Widgets::class,
        ]);

        $panel->widgets([
            StatsDemoWidget::class,
            LineChartDemoWidget::class,
            BarChartDemoWidget::class,
            DoughnutChartDemoWidget::class,
        ]);

        $this->applyPanelConfig($panel);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Apply config('design-system.panel') overrides to the host panel.
     *
     * Each key is conditional — null/empty values are skipped so the panel
     * falls back to Filament defaults. Order matches the admin panel provider
     * convention so a side-by-side diff between the two reads naturally.
     */
    protected function applyPanelConfig(Panel $panel): void
    {
        // Read from the resolved panel layer so JSON-overlay edits (via the
        // MCP write_tokens tool's `panel` subtree) take effect alongside the
        // PHP config defaults.
        $config = Tokens::resolvedPanel();

        if ($theme = $config['vite_theme'] ?? null) {
            $panel->viteTheme($theme);
        }

        if (! empty($config['colors'] ?? [])) {
            $panel->colors($config['colors']);
        }

        if (($mode = $config['default_theme_mode'] ?? null) !== null) {
            $panel->defaultThemeMode(
                $mode instanceof ThemeMode ? $mode : ThemeMode::from($mode),
            );
        }

        if ($maxWidth = $config['max_content_width'] ?? null) {
            $panel->maxContentWidth($maxWidth);
        }

        if ($family = $config['font']['family'] ?? null) {
            $provider = $config['font']['provider'] ?? null;

            $provider
                ? $panel->font($family, provider: $provider)
                : $panel->font($family);
        }

        if ($logo = $config['brand']['logo'] ?? null) {
            $panel->brandLogo($this->resolveAssetUrl($logo));
        }

        if ($logoDark = $config['brand']['logo_dark'] ?? null) {
            $panel->darkModeBrandLogo($this->resolveAssetUrl($logoDark));
        }

        if ($height = $config['brand']['logo_height'] ?? null) {
            $panel->brandLogoHeight($height);
        }

        if ($favicon = $config['brand']['favicon'] ?? null) {
            $panel->favicon($this->resolveAssetUrl($favicon));
        }
    }

    /**
     * Brand keys accept either an absolute URL (passed through as-is) or a path
     * relative to /public (passed through asset()). Done at panel-register time
     * so the URL generator is fully bootstrapped — calling asset() inside the
     * config file fails during early test boot.
     */
    protected function resolveAssetUrl(string $value): string
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//')
            ? $value
            : asset($value, secure: true);
    }
}
