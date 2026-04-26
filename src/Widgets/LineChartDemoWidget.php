<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Widgets;

use Filament\Widgets\ChartWidget;

class LineChartDemoWidget extends ChartWidget
{
    protected ?string $heading = 'Monthly orders';

    protected ?string $description = 'Two-series line chart over twelve months.';

    protected function getData(): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => [180, 215, 248, 267, 312, 290, 335, 360, 388, 412, 405, 442],
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Returning customers',
                    'data' => [62, 78, 88, 105, 120, 118, 135, 142, 158, 172, 168, 184],
                    'tension' => 0.35,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }
}
