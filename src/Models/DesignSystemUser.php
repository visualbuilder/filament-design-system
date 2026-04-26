<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DesignSystemUser extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $table = 'design_system_users';

    protected $fillable = ['name', 'email', 'password', 'avatar_url'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'design-system';
    }

    public function getFilamentName(): string
    {
        return $this->name ?? 'Design Reviewer';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }
}
