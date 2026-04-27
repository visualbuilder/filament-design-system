<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Resources;

use Filament\Resources\Resource;
use Visualbuilder\FilamentDesignSystem\Models\DesignSystemUser;
use Visualbuilder\FilamentDesignSystem\Resources\DesignSystemUserResource\Pages;

/**
 * Minimal resource backing the demo users table. Its only purpose is to
 * give the panel something globally searchable so the topbar's global-search
 * input renders — Filament hides that input on panels with no searchable
 * resources. Hidden from sidebar navigation; pink26 designers can still
 * iterate on the search-pill chrome via the topbar.
 */
class DesignSystemUserResource extends Resource
{
    protected static ?string $model = DesignSystemUser::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = false;

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDesignSystemUsers::route('/'),
        ];
    }

    /**
     * Bypass policy resolution. The DesignSystemUser model does not use the
     * host application's permission stack (Spatie HasRoles, etc.) — letting
     * the default Filament auth resolve a policy would invoke methods like
     * hasRole() that don't exist on this model. The resource is read-only
     * by design (its only purpose is to power global search), so allowing
     * view + denying mutations is the right contract.
     */
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView($record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Email' => $record->email,
        ];
    }
}
