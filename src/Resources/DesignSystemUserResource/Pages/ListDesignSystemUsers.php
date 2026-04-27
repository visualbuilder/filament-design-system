<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Resources\DesignSystemUserResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Visualbuilder\FilamentDesignSystem\Resources\DesignSystemUserResource;

class ListDesignSystemUsers extends ListRecords
{
    protected static string $resource = DesignSystemUserResource::class;
}
