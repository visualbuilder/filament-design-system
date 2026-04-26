<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Database\Seeders;

use Illuminate\Database\Seeder;
use Visualbuilder\FilamentDesignSystem\Models\DesignSystemUser;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        DesignSystemUser::query()->updateOrCreate(
            ['email' => config('design-system.demo_user.email')],
            [
                'name' => config('design-system.demo_user.name'),
                'password' => config('design-system.demo_user.password'),
            ],
        );
    }
}
