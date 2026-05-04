<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        // Prefill is opt-out via config('design-system.demo_user.enabled')
        // — production / internal-tool hosts can set false to avoid
        // surfacing seeded demo creds on the real login form.
        if (! config('design-system.demo_user.enabled', true)) {
            return;
        }

        $this->form->fill([
            'email' => config('design-system.demo_user.email'),
            'password' => config('design-system.demo_user.password'),
            'remember' => true,
        ]);
    }
}
