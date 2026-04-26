<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Visualbuilder\FilamentDesignSystem\Http\Controllers\ScreenshotSessionController;

Route::middleware(['web'])->group(function (): void {
    Route::get('_filament-design-system/screenshot/{page}', ScreenshotSessionController::class)
        ->name('filament-design-system.screenshot');
});
