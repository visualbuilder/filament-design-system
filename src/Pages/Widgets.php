<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;
use Visualbuilder\FilamentDesignSystem\Widgets\BarChartDemoWidget;
use Visualbuilder\FilamentDesignSystem\Widgets\DoughnutChartDemoWidget;
use Visualbuilder\FilamentDesignSystem\Widgets\LineChartDemoWidget;
use Visualbuilder\FilamentDesignSystem\Widgets\StatsDemoWidget;

class Widgets extends Dashboard
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $title = 'Widgets';

    protected static ?int $navigationSort = 60;

    protected static string $routePath = 'widgets';

    public function getColumns(): int|array
    {
        return ['default' => 1, 'md' => 2];
    }

    public function getWidgets(): array
    {
        return [
            StatsDemoWidget::class,
            LineChartDemoWidget::class,
            BarChartDemoWidget::class,
            DoughnutChartDemoWidget::class,
        ];
    }
}
