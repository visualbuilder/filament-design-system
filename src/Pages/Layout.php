<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\UnorderedList;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;

class Layout extends Page
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $title = 'Layout';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament-design-system::pages.layout';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'aside_field' => 'Lee Evans',
            'compact_field' => 'Tighter spacing',
            'fieldset_a' => 'Apple',
            'fieldset_b' => 'Banana',
            'wizard_name' => 'Demo project',
            'wizard_owner' => 'lee@neurohub.uk',
            'fused_protocol' => 'https',
            'fused_domain' => 'design.neurohub.local',
            'flex_left' => 'Left',
            'flex_right' => 'Right',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Layout components')
                    ->contained(false)
                    ->tabs([
                        $this->sectionsTab(),
                        $this->tabsAndWizardsTab(),
                        $this->gridAndGroupsTab(),
                        $this->contentTab(),
                    ])
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('demoSubmit')
                ->label('Submit (demo)')
                ->submit('demoSubmit'),
        ];
    }

    public function demoSubmit(): void
    {
        Notification::make()
            ->title('Demo flow')
            ->body('Nothing was saved — the design-system panel never persists data.')
            ->success()
            ->send();
    }

    protected function sectionsTab(): Tab
    {
        return Tab::make('Sections & Fieldsets')
            ->icon(Heroicon::OutlinedRectangleStack)
            ->schema([
                Section::make('Default Section (with a Secondary nested inside)')
                    ->description('The default Section is the standard container. The Secondary Section below sits nested inside this one so the surface contrast is visible — when both render at the page level the difference is subtle, especially in dark mode.')
                    ->schema([
                        TextInput::make('default_field')->label('A field inside the default section'),

                        Section::make('Secondary Section')
                            ->description('Subdued background — visually de-emphasised against its parent default Section.')
                            ->secondary()
                            ->schema([
                                TextInput::make('secondary_field')->label('A field inside the secondary section'),
                            ]),
                    ]),

                Section::make('Section with aside')
                    ->description('Title and description sit to the left, content to the right. Good for grouping settings forms.')
                    ->aside()
                    ->schema([
                        TextInput::make('aside_field')->label('Display name'),
                        Toggle::make('aside_toggle')->label('Public profile'),
                    ]),

                Section::make('Collapsible (collapsed by default)')
                    ->description('Click the title to expand. Useful for advanced/optional groups.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('collapsed_field')->label('Hidden until expanded'),
                    ]),

                Section::make('Compact')
                    ->description('Tighter padding — good for dense forms or sub-sections.')
                    ->compact()
                    ->schema([
                        TextInput::make('compact_field')->label('Compact field'),
                    ]),

                Section::make('Section with header actions')
                    ->description('Actions can sit alongside the section title.')
                    ->headerActions([
                        Action::make('reset_section')
                            ->label('Reset')
                            ->icon(Heroicon::OutlinedArrowPath)
                            ->color('gray')
                            ->action(fn () => Notification::make()->title('Reset (demo)')->info()->send()),
                        Action::make('learn_more')
                            ->label('Learn more')
                            ->icon(Heroicon::OutlinedQuestionMarkCircle)
                            ->color('gray')
                            ->action(fn () => Notification::make()->title('Demo action')->info()->send()),
                    ])
                    ->schema([
                        TextInput::make('header_actions_field')->label('Settings'),
                    ]),

                Section::make('Section with footer actions')
                    ->description('Actions render in a footer strip below the body — typical for save/cancel pairings or destructive actions kept away from the title bar. footerActionsAlignment(Alignment::End) right-aligns them.')
                    ->footerActions([
                        Action::make('cancel_footer')
                            ->label('Cancel')
                            ->color('gray')
                            ->outlined()
                            ->action(fn () => Notification::make()->title('Cancelled (demo)')->info()->send()),
                        Action::make('save_footer')
                            ->label('Save changes')
                            ->icon(Heroicon::OutlinedCheck)
                            ->color('primary')
                            ->action(fn () => Notification::make()->title('Saved (demo)')->success()->send()),
                    ])
                    ->footerActionsAlignment(Alignment::End)
                    ->schema([
                        TextInput::make('footer_actions_field')->label('Display name'),
                        TextInput::make('footer_actions_email')->label('Reply-to email')->email(),
                    ]),

                Fieldset::make('Fieldset')
                    ->schema([
                        TextInput::make('fieldset_a')->label('First name'),
                        TextInput::make('fieldset_b')->label('Last name'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function tabsAndWizardsTab(): Tab
    {
        return Tab::make('Tabs & Wizards')
            ->icon(Heroicon::OutlinedQueueList)
            ->schema([
                Tabs::make('Inline tabs demo')
                    ->tabs([
                        Tab::make('General')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
                                Text::make('Inline tabs are a Filament Tabs schema component used as a content container — separate from the page-level tabs at the top. Switch between General, Notifications, and Danger zone to see how the same body area swaps without a page reload.'),
                                TextInput::make('inline_general_name')->label('Name'),
                                Textarea::make('inline_general_about')->label('About'),
                            ]),

                        Tab::make('Notifications')
                            ->icon(Heroicon::OutlinedBell)
                            ->badge('3')
                            ->schema([
                                Toggle::make('inline_email_alerts')->label('Email alerts'),
                                Toggle::make('inline_sms_alerts')->label('SMS alerts'),
                            ]),

                        Tab::make('Danger zone')
                            ->icon(Heroicon::OutlinedExclamationTriangle)
                            ->schema([
                                Callout::make('Permanent actions live here')
                                    ->description('Anything destructive — delete account, revoke tokens — would sit in this tab.')
                                    ->danger(),
                            ]),
                    ]),

                Wizard::make([
                    Step::make('Project')
                        ->icon(Heroicon::OutlinedFolder)
                        ->description('Name and owner')
                        ->schema([
                            Text::make('A Wizard steps a user through a multi-stage flow with forward/back navigation and per-step validation. This first step collects basic project details — required fields must be valid before the Next button enables step 2.'),
                            TextInput::make('wizard_name')
                                ->label('Project name')
                                ->required(),
                            TextInput::make('wizard_owner')
                                ->label('Owner email')
                                ->email()
                                ->required(),
                        ]),

                    Step::make('Settings')
                        ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                        ->description('Configure behaviour')
                        ->schema([
                            Select::make('wizard_visibility')
                                ->label('Visibility')
                                ->options(['public' => 'Public', 'private' => 'Private'])
                                ->default('private'),
                            Toggle::make('wizard_notify')->label('Notify team on changes'),
                        ]),

                    Step::make('Review')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->description('Confirm and submit')
                        ->schema([
                            Text::make('Press Submit to fire a demo notification — nothing is persisted.'),
                        ]),
                ])->skippable(),
            ]);
    }

    protected function gridAndGroupsTab(): Tab
    {
        return Tab::make('Grid & Groups')
            ->icon(Heroicon::OutlinedTableCells)
            ->schema([
                Section::make('Grid (2 columns)')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('grid_two_a')->label('Field A'),
                            TextInput::make('grid_two_b')->label('Field B'),
                        ]),
                    ]),

                Section::make('Grid (responsive)')
                    ->description('1 column on mobile, 2 on md, 4 on lg.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2, 'lg' => 4])
                            ->schema([
                                TextInput::make('grid_resp_a')->label('A'),
                                TextInput::make('grid_resp_b')->label('B'),
                                TextInput::make('grid_resp_c')->label('C'),
                                TextInput::make('grid_resp_d')->label('D'),
                            ]),
                    ]),

                Section::make('Group')
                    ->description('An invisible container — useful for column-span control.')
                    ->columns(3)
                    ->schema([
                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('group_address')->label('Address'),
                                TextInput::make('group_city')->label('City'),
                            ]),
                        TextInput::make('group_postcode')
                            ->label('Postcode')
                            ->columnSpan(1),
                    ]),

                Section::make('FusedGroup')
                    ->description('Inputs visually fused — borders join across the boundary.')
                    ->schema([
                        FusedGroup::make([
                            Select::make('fused_protocol')
                                ->options(['http' => 'http://', 'https' => 'https://'])
                                ->default('https'),
                            TextInput::make('fused_domain')
                                ->placeholder('example.com'),
                        ])
                            ->label('Site URL')
                            ->columns([
                                'default' => 5,
                                'sm' => 5,
                            ]),
                    ]),

                Section::make('Flex')
                    ->description('Flexbox-driven horizontal layout.')
                    ->schema([
                        Flex::make([
                            TextInput::make('flex_left')->label('Left'),
                            TextInput::make('flex_right')->label('Right'),
                        ]),
                    ]),
            ]);
    }

    protected function contentTab(): Tab
    {
        return Tab::make('Content')
            ->icon(Heroicon::OutlinedBookOpen)
            ->schema([
                Section::make('Callouts')
                    ->description('Inline messages with semantic colours.')
                    ->schema([
                        Callout::make('Info')
                            ->description('Callouts are useful for inline tips that don\'t warrant a full notification.')
                            ->info(),

                        Callout::make('Success')
                            ->description('You\'re on the latest version.')
                            ->success(),

                        Callout::make('Warning')
                            ->description('Permission changes take effect after the next login.')
                            ->warning(),

                        Callout::make('Danger')
                            ->description('Two tokens are about to expire.')
                            ->danger(),
                    ]),

                Section::make('Text & HTML')
                    ->schema([
                        Text::make('Text component — plain content rendered through the schema. Inherits the panel\'s body type.'),
                        Html::make('<p style="margin-top: 0.5rem;">Html component — accepts <strong>raw HTML</strong> when you need <em>formatting</em> beyond what Text supports.</p>'),
                    ]),

                Section::make('UnorderedList')
                    ->schema([
                        UnorderedList::make([
                            'First bullet — items are rendered with consistent spacing and the panel\'s list bullet.',
                            'Second bullet — useful for short prose lists inside a Section.',
                            'Third bullet — and you can mix in arbitrary content.',
                        ]),
                    ]),
            ]);
    }
}
