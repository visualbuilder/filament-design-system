<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem;

use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Laravel\Mcp\Facades\Mcp;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Visualbuilder\FilamentDesignSystem\Mcp\DesignSystemServer;
use Visualbuilder\FilamentDesignSystem\Theme\Tokens;

class FilamentDesignSystemServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-design-system')
            ->hasConfigFile('design-system')
            ->hasMigration('create_design_system_users_table')
            ->runsMigrations()
            ->hasRoute('web')
            ->hasViews('filament-design-system');
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        $this->registerTokenStylesheet();
        $this->registerLoginCredentialsCallout();
        $this->registerMcpServer();
        $this->registerPublishables();
    }

    protected function registerMcpServer(): void
    {
        if (! class_exists(Mcp::class)) {
            return;
        }

        Mcp::local('design-system', DesignSystemServer::class);
    }

    /**
     * Inject CSS variables emitted from config('design-system.tokens') into the panel head.
     * This is what makes a token edit cascade across every catalogue page.
     */
    protected function registerTokenStylesheet(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            function (): string {
                if (Filament::getCurrentPanel()?->getId() !== 'design-system') {
                    return '';
                }

                return Blade::render(
                    '<style id="design-system-tokens">{!! $css !!}</style>',
                    ['css' => Tokens::toCss()],
                );
            },
        );
    }

    protected function registerLoginCredentialsCallout(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
            function (): string {
                if (Filament::getCurrentPanel()?->getId() !== 'design-system') {
                    return '';
                }

                return Blade::render('<x-filament-design-system::login-credentials-callout />');
            },
        );
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../stubs/DesignSystemPanelProvider.stub'
                => app_path('Providers/Filament/DesignSystemPanelProvider.php'),
        ], 'filament-design-system-provider');

        $this->publishes([
            __DIR__ . '/../database/seeders/DemoUserSeeder.php'
                => database_path('seeders/DesignSystemDemoUserSeeder.php'),
        ], 'filament-design-system-seeders');
    }
}
