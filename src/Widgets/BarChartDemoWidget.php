<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Widgets;

use Filament\Widgets\ChartWidget;

class BarChartDemoWidget extends ChartWidget
{
    protected ?string $heading = 'Orders by status';

    protected ?string $description = 'Snapshot of order pipeline.';

    protected function getData(): array
    {
        return [
            'labels' => ['Draft', 'Pending', 'In review', 'Completed', 'Cancelled'],
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => [22, 48, 14, 142, 9],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
