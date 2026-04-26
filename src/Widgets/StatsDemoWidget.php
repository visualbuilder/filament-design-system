<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsDemoWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Demo stats';

    protected ?string $description = 'Hard-coded values — useful for previewing card chrome, sparkline colour, and status icons.';

    protected function getStats(): array
    {
        return [
            Stat::make('Revenue', '£24,189')
                ->description('12% up this month')
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
                ->descriptionColor('success')
                ->chart([7, 12, 9, 15, 18, 21, 19, 24, 28, 26, 30, 33])
                ->chartColor('success')
                ->icon(Heroicon::OutlinedBanknotes),

            Stat::make('Active orders', '142')
                ->description('8 pending review')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->descriptionColor('warning')
                ->chart([18, 22, 17, 24, 19, 28, 22, 26, 23, 29, 31, 28])
                ->chartColor('warning')
                ->icon(Heroicon::OutlinedDocumentText),

            Stat::make('Completed', '1,832')
                ->description('+184 this week')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->descriptionColor('success')
                ->chart([5, 8, 12, 9, 15, 18, 21, 24, 28, 31, 33, 36])
                ->chartColor('primary')
                ->icon(Heroicon::OutlinedCheckBadge),

            Stat::make('Cancelled', '28')
                ->description('3% down this month')
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingDown)
                ->descriptionColor('danger')
                ->chart([22, 19, 24, 21, 17, 18, 14, 16, 12, 10, 9, 7])
                ->chartColor('danger')
                ->icon(Heroicon::OutlinedXCircle),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
