<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Visualbuilder\FilamentDesignSystem\Theme\Tokens;

class Index extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?string $title = 'Overview';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament-design-system::pages.index';

    public function getViewData(): array
    {
        $tokens = Tokens::resolved();
        $panel = Tokens::resolvedPanel();

        return [
            'typography' => $tokens['typography'] ?? [],
            'panel_font' => $panel['font']['family'] ?? 'Filament default (system sans-serif)',
            'colors' => $tokens['colors'] ?? [],
            'colour_sections' => config('design-system.catalogue.colour_sections', []),
            'spacing' => $tokens['spacing'] ?? [],
            'radius' => $tokens['radius'] ?? [],
            'shadow' => $tokens['shadow'] ?? [],
            'flat' => Tokens::flat(),
        ];
    }
}
