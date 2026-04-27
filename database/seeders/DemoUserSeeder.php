<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Database\Seeders;

use Illuminate\Database\Seeder;
use Visualbuilder\FilamentDesignSystem\Models\DesignSystemUser;

class DemoUserSeeder extends Seeder
{
    /**
     * Extra demo records used to populate the panel's global search results
     * (the DesignSystemUserResource exists solely to surface the search
     * input). Names are intentionally varied so search can be demoed.
     *
     * @var array<int, array{name: string, email: string}>
     */
    protected const ADDITIONAL_DEMO_USERS = [
        ['name' => 'Liu Wei', 'email' => 'liu.wei@example.test'],
        ['name' => 'Amara Okafor', 'email' => 'amara.okafor@example.test'],
        ['name' => 'Sebastian Nilsson', 'email' => 'sebastian.nilsson@example.test'],
        ['name' => 'Priya Rao', 'email' => 'priya.rao@example.test'],
        ['name' => 'Mateo Fernández', 'email' => 'mateo.fernandez@example.test'],
        ['name' => 'Zoe Chadwick', 'email' => 'zoe.chadwick@example.test'],
        ['name' => 'Idris Khan', 'email' => 'idris.khan@example.test'],
        ['name' => 'Sofia Romano', 'email' => 'sofia.romano@example.test'],
    ];

    public function run(): void
    {
        DesignSystemUser::query()->updateOrCreate(
            ['email' => config('design-system.demo_user.email')],
            [
                'name' => config('design-system.demo_user.name'),
                'password' => config('design-system.demo_user.password'),
            ],
        );

        foreach (static::ADDITIONAL_DEMO_USERS as $user) {
            DesignSystemUser::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'design-system',
                ],
            );
        }
    }
}
