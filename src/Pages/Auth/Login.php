<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => config('design-system.demo_user.email'),
            'password' => config('design-system.demo_user.password'),
            'remember' => true,
        ]);
    }
}
