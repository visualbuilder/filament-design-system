<?php

declare(strict_types=1);

/*
 * Tokens are the single source of truth for the design system.
 *
 * Values flow through Theme\Tokens::toCss() into a <style id="design-system-tokens">
 * tag injected into the panel head. Every catalogue page and every Filament
 * component renders against these CSS variables, so a single edit cascades.
 *
 * Keep this file LLM-friendly: flat keys, hex colours, no closures.
 */

return [
    'demo_user' => [
        // When true (default), the design-system panel:
        //   - prefills the login form with the email/password below
        //   - renders a "Demo credentials (pre-filled)" callout above the form
        //
        // Both behaviours suit a standalone demo install. Production hosts
        // mounting the panel internally (e.g. for QA / screenshot review)
        // should set this to false so real reviewers don't see seeded demo
        // creds on their login form.
        'enabled' => env('DESIGN_SYSTEM_DEMO_ENABLED', true),

        'name' => env('DESIGN_SYSTEM_DEMO_NAME', 'Design Reviewer'),
        'email' => env('DESIGN_SYSTEM_DEMO_EMAIL', 'designer@example.test'),
        'password' => env('DESIGN_SYSTEM_DEMO_PASSWORD', 'design-system'),
    ],

    /*
     * Panel chrome — applied by FilamentDesignSystemPlugin::register() onto the
     * panel that mounts this plugin. Set values here to match the panel your
     * designer is iterating against (typically the admin panel) so the
     * catalogue renders against the existing visual baseline.
     *
     * Any key set to null is skipped — the panel falls back to Filament defaults.
     */
    'panel' => [
        'vite_theme' => null,                // e.g. 'resources/css/filament/admin/theme.css'
        'colors' => null,                    // e.g. config('company.theme_colors')
        'default_theme_mode' => null,        // 'light' | 'dark' | 'system'
        'max_content_width' => null,         // e.g. 'screen-4xl'

        'font' => [
            'family' => null,                // e.g. 'Roboto'
            'provider' => null,              // e.g. \Filament\FontProviders\GoogleFontProvider::class
        ],

        'brand' => [
            'logo' => null,                  // light-mode logo URL
            'logo_dark' => null,             // dark-mode logo URL
            'logo_height' => null,           // e.g. '2.5rem'
            'favicon' => null,               // favicon URL
        ],
    ],

    'tokens' => [
        // A flat map of palette keys to either a shade-array (e.g. [50 => '#…', …, 950 => '#…'])
        // or a single colour string. Keys are unique and become the namespace for emitted
        // CSS variables (--color-{key}-{shade} or --color-{key}). Consumers add/rename
        // keys here to match their host palette.
        'colors' => [
            'primary' => [
                '50' => '#fff0fd', '100' => '#ffe3fb', '200' => '#ffc6f6',
                '300' => '#ff96ef', '400' => '#ff56e5', '500' => '#f020d4',
                '600' => '#e040fb', '700' => '#b800c7', '800' => '#93009e',
                '900' => '#6e0076', '950' => '#440047',
            ],
            'success' => ['400' => '#4ade80', '500' => '#22c55e', '600' => '#16a34a'],
            'warning' => ['400' => '#fbbf24', '500' => '#f59e0b', '600' => '#d97706'],
            'danger' => ['400' => '#f87171', '500' => '#ef4444', '600' => '#dc2626'],
            'info' => ['400' => '#60a5fa', '500' => '#3b82f6', '600' => '#2563eb'],
            'gray' => [
                '50' => '#f9fafb', '100' => '#f3f4f6', '200' => '#e5e7eb',
                '300' => '#d1d5db', '400' => '#9ca3af', '500' => '#6b7280',
                '600' => '#4b5563', '700' => '#374151', '800' => '#1f2937',
                '900' => '#111827', '950' => '#030712',
            ],
        ],

        'typography' => [
            'family' => [
                'body' => 'Nunito, sans-serif',
                'heading' => 'Nunito, sans-serif',
            ],
            'size' => [
                'xs' => '0.75rem', 'sm' => '0.875rem', 'base' => '1rem',
                'lg' => '1.125rem', 'xl' => '1.25rem', '2xl' => '1.5rem',
                '3xl' => '1.875rem', '4xl' => '2.25rem', '5xl' => '3rem',
            ],
            'weight' => [
                'extralight' => 200, 'light' => 300, 'regular' => 400,
                'medium' => 500, 'semibold' => 600, 'bold' => 700, 'black' => 900,
                'heading' => 300,
            ],
            'leading' => [
                'tight' => '1.25', 'normal' => '1.5', 'relaxed' => '1.625',
            ],
        ],

        'spacing' => [
            '1' => '0.25rem', '2' => '0.5rem', '3' => '0.75rem',
            '4' => '1rem', '5' => '1.25rem', '6' => '1.5rem',
            '8' => '2rem', '12' => '3rem', '16' => '4rem',
        ],

        'radius' => [
            'sm' => '0.25rem', 'md' => '0.375rem', 'lg' => '0.5rem',
            'xl' => '0.75rem', '2xl' => '1rem', 'full' => '9999px',
        ],

        'shadow' => [
            'sm' => '0 1px 2px 0 rgb(0 0 0 / 0.4)',
            'md' => '0 4px 6px -1px rgb(0 0 0 / 0.4), 0 2px 4px -2px rgb(0 0 0 / 0.3)',
            'lg' => '0 10px 15px -3px rgb(0 0 0 / 0.4), 0 4px 6px -4px rgb(0 0 0 / 0.3)',
        ],
    ],

    /*
     * Catalogue display layout. Sections are rendered top-to-bottom on the
     * Index page. Each section references palette keys defined in tokens.colors.
     * Unknown keys are skipped silently — safe to leave host-specific entries in
     * the package default that don't apply.
     */
    'catalogue' => [
        'colour_sections' => [
            [
                'title' => 'Primary',
                'description' => 'The brand primary palette.',
                'palettes' => ['primary'],
            ],
            [
                'title' => 'Semantic',
                'description' => 'Status colours — success, warning, danger, info.',
                'palettes' => ['success', 'warning', 'danger', 'info'],
            ],
            [
                'title' => 'Neutral',
                'palettes' => ['gray'],
            ],
        ],
    ],
];
