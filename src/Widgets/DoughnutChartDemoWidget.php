<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Widgets;

use Filament\Widgets\ChartWidget;

class DoughnutChartDemoWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue by service';

    protected ?string $description = 'Demo distribution across service categories.';

    protected function getData(): array
    {
        $tokens = config('design-system.tokens.colors', []);

        // Pull five distinct slice colours from the live tokens. Edits to the
        // tokens config (or company.theme_colors via the published file)
        // cascade straight through to the chart.
        $palette = array_filter([
            data_get($tokens, 'primary.500'),
            data_get($tokens, 'success.500'),
            data_get($tokens, 'warning.500'),
            data_get($tokens, 'danger.500'),
            data_get($tokens, 'info.500'),
        ]);

        return [
            'labels' => ['Coaching', 'Assessments', 'Training', 'Assistive tech', 'Adjustments'],
            'datasets' => [
                [
                    'label' => 'Share',
                    'data' => [42, 28, 18, 8, 4],
                    'backgroundColor' => array_values($palette),
                    'borderWidth' => 0,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
