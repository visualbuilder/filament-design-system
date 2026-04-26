<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Infolists extends Page
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $title = 'Infolists';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament-design-system::pages.infolists';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Infolist entries')
                    ->contained(false)
                    ->tabs([
                        $this->textEntriesTab(),
                        $this->iconsAndImagesTab(),
                        $this->compositeTab(),
                        $this->specialisedTab(),
                    ])
                    ->persistTabInQueryString(),
            ]);
    }

    protected function textEntriesTab(): Tab
    {
        return Tab::make('Text entries')
            ->icon(Heroicon::OutlinedDocumentText)
            ->schema([
                Section::make('Basic')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('basic')
                            ->label('Plain')
                            ->state('A simple text entry — value rendered against the panel\'s body type.'),

                        TextEntry::make('with_helper')
                            ->label('Reference')
                            ->state('NB-1042')
                            ->helperText('Helper text appears below the entry — useful for unit hints or context.')
                            ->copyable(),

                        TextEntry::make('weight_semibold')
                            ->label('Customer name')
                            ->state('Lee Evans')
                            ->weight('semibold'),

                        TextEntry::make('with_icon')
                            ->label('Verified email')
                            ->state('lee@example.test')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->iconColor('primary'),
                    ]),

                Section::make('Formatted')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('money')
                            ->label('Total')
                            ->state(1234.56)
                            ->money('GBP'),

                        TextEntry::make('date')
                            ->label('Created')
                            ->state(now()->subDays(3))
                            ->date('M j, Y'),

                        TextEntry::make('datetime')
                            ->label('Last login')
                            ->state(now()->subHours(2))
                            ->dateTime('M j, Y · H:i'),

                        TextEntry::make('prefix_suffix')
                            ->label('Latency')
                            ->state(284)
                            ->suffix(' ms'),
                    ]),

                Section::make('Badge variants')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('badge_status')
                            ->label('Status')
                            ->state('Completed')
                            ->badge()
                            ->color('success')
                            ->icon(Heroicon::OutlinedCheckCircle),

                        TextEntry::make('badge_role')
                            ->label('Role')
                            ->state('Administrator')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('badge_priority')
                            ->label('Priority')
                            ->state('High')
                            ->badge()
                            ->color('danger')
                            ->icon(Heroicon::OutlinedExclamationTriangle),

                        TextEntry::make('badge_draft')
                            ->label('Draft')
                            ->state('Draft')
                            ->badge()
                            ->color('gray'),
                    ]),

                Section::make('Multi-value list')
                    ->schema([
                        TextEntry::make('tags')
                            ->label('Tags')
                            ->state(['design-system', 'filament', 'theming', 'accessibility'])
                            ->badge()
                            ->color('info')
                            ->separator(','),

                        TextEntry::make('roles')
                            ->label('Roles')
                            ->state(['Reviewer', 'Editor', 'Publisher'])
                            ->listWithLineBreaks()
                            ->bulleted(),
                    ]),
            ]);
    }

    protected function iconsAndImagesTab(): Tab
    {
        return Tab::make('Icons & images')
            ->icon(Heroicon::OutlinedPhoto)
            ->schema([
                Section::make('Icon entries')
                    ->description('Boolean values rendered as semantic icons.')
                    ->columns(3)
                    ->schema([
                        IconEntry::make('verified')
                            ->label('Email verified')
                            ->state(true)
                            ->boolean(),

                        IconEntry::make('two_factor')
                            ->label('2FA enabled')
                            ->state(false)
                            ->boolean(),

                        IconEntry::make('priority')
                            ->label('Priority')
                            ->state(true)
                            ->boolean()
                            ->trueIcon(Heroicon::OutlinedStar)
                            ->falseIcon(Heroicon::Star)
                            ->trueColor('warning')
                            ->falseColor('gray'),
                    ]),

                Section::make('Image entry')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('avatar')
                            ->label('Avatar')
                            ->state('https://api.dicebear.com/7.x/initials/svg?seed=Lee+Evans')
                            ->circular()
                            ->size(96),

                        ImageEntry::make('cover')
                            ->label('Cover image')
                            ->state('https://api.dicebear.com/7.x/shapes/svg?seed=neurohub-cover&backgroundType=gradientLinear')
                            ->extraAttributes(['style' => 'border-radius: 0.5rem; max-width: 280px;']),
                    ]),
            ]);
    }

    protected function compositeTab(): Tab
    {
        return Tab::make('Composite')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->schema([
                Section::make('Key-Value entry')
                    ->description('A flat map rendered as two-column rows. Useful for metadata blobs.')
                    ->schema([
                        KeyValueEntry::make('metadata')
                            ->label('Webhook payload')
                            ->state([
                                'event' => 'order.completed',
                                'reference' => 'NB-1042',
                                'amount' => '£124.50',
                                'currency' => 'GBP',
                                'received_at' => '2026-04-26 14:02 UTC',
                            ]),
                    ]),

                Section::make('Repeatable entry')
                    ->description('A list of records, each rendered against a nested schema.')
                    ->schema([
                        RepeatableEntry::make('contacts')
                            ->label('Order contacts')
                            ->state([
                                ['name' => 'Lee Evans', 'role' => 'Primary', 'email' => 'lee@example.test'],
                                ['name' => 'Sam Carter', 'role' => 'Billing', 'email' => 'sam@example.test'],
                                ['name' => 'Alex Rivera', 'role' => 'Technical', 'email' => 'alex@example.test'],
                            ])
                            ->schema([
                                TextEntry::make('name')->weight('semibold'),
                                TextEntry::make('role')->badge()->color('primary'),
                                TextEntry::make('email')->icon(Heroicon::OutlinedEnvelope),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    protected function specialisedTab(): Tab
    {
        return Tab::make('Specialised')
            ->icon(Heroicon::OutlinedSparkles)
            ->schema([
                Section::make('Colour entry')
                    ->description('Renders the value as a swatch — useful for showing a stored colour value.')
                    ->columns(3)
                    ->schema([
                        ColorEntry::make('brand_primary')
                            ->label('Brand primary')
                            ->state('#664dff')
                            ->copyable(),

                        ColorEntry::make('brand_warning')
                            ->label('Warning')
                            ->state('#f59e0b')
                            ->copyable(),

                        ColorEntry::make('brand_danger')
                            ->label('Danger')
                            ->state('#dc2626')
                            ->copyable(),
                    ]),

                Section::make('Code-formatted text')
                    ->description('A preformatted block via TextEntry. Filament also ships a CodeEntry with syntax highlighting (requires the optional phiki/phiki package).')
                    ->schema([
                        TextEntry::make('payload')
                            ->label('Sample JSON')
                            ->state(json_encode([
                                'event' => 'order.completed',
                                'data' => [
                                    'reference' => 'NB-1042',
                                    'customer' => 'Lee Evans',
                                    'total' => 124.50,
                                ],
                            ], JSON_PRETTY_PRINT))
                            ->extraAttributes([
                                'style' => 'font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.8125rem; white-space: pre; background: rgba(127,127,127,0.06); padding: 0.75rem 1rem; border-radius: 0.375rem; display: block;',
                            ]),
                    ]),
            ]);
    }
}
