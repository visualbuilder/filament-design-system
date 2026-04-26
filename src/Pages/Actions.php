<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions as ActionsComponent;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;

class Actions extends Page
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static ?string $title = 'Actions & Buttons';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament-design-system::pages.actions';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Actions')
                    ->contained(false)
                    ->tabs([
                        $this->variantsTab(),
                        $this->iconsAndStatesTab(),
                        $this->modalsTab(),
                        $this->notificationsTab(),
                        $this->groupsTab(),
                    ])
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    protected function variantsTab(): Tab
    {
        return Tab::make('Variants')
            ->icon(Heroicon::OutlinedSwatch)
            ->schema([
                Section::make('Colours')
                    ->description('Each semantic colour applied to an otherwise identical solid button.')
                    ->schema([
                        ActionsComponent::make([
                            $this->demoAction('colour_primary', 'Primary')->color('primary'),
                            $this->demoAction('colour_success', 'Success')->color('success'),
                            $this->demoAction('colour_warning', 'Warning')->color('warning'),
                            $this->demoAction('colour_danger', 'Danger')->color('danger'),
                            $this->demoAction('colour_info', 'Info')->color('info'),
                            $this->demoAction('colour_gray', 'Gray')->color('gray'),
                        ]),
                    ]),

                Section::make('Sizes')
                    ->description('All five sizes from the Size enum, primary colour for direct comparison.')
                    ->schema([
                        ActionsComponent::make([
                            $this->demoAction('size_xs', 'Extra small')->size(Size::ExtraSmall),
                            $this->demoAction('size_sm', 'Small')->size(Size::Small),
                            $this->demoAction('size_md', 'Medium')->size(Size::Medium),
                            $this->demoAction('size_lg', 'Large')->size(Size::Large),
                            $this->demoAction('size_xl', 'Extra large')->size(Size::ExtraLarge),
                        ]),
                    ]),

                Section::make('Styles')
                    ->description('Solid (default), outlined, and link — same colour, different chrome weight.')
                    ->schema([
                        ActionsComponent::make([
                            $this->demoAction('style_solid', 'Solid'),
                            $this->demoAction('style_outlined', 'Outlined')->outlined(),
                            $this->demoAction('style_link', 'Link')->link(),
                        ]),
                    ]),
            ]);
    }

    protected function iconsAndStatesTab(): Tab
    {
        return Tab::make('Icons & states')
            ->icon(Heroicon::OutlinedStar)
            ->schema([
                Section::make('With icons')
                    ->description('Leading icon, trailing icon, and icon-only.')
                    ->schema([
                        ActionsComponent::make([
                            $this->demoAction('icon_leading', 'Save')
                                ->icon(Heroicon::OutlinedCheck),
                            $this->demoAction('icon_trailing', 'Continue')
                                ->icon(Heroicon::OutlinedArrowRight)
                                ->iconPosition(\Filament\Support\Enums\IconPosition::After),
                            $this->demoAction('icon_only', 'Refresh')
                                ->icon(Heroicon::OutlinedArrowPath)
                                ->iconButton()
                                ->tooltip('Refresh data'),
                        ]),
                    ]),

                Section::make('Disabled')
                    ->description('Disabled buttons across colours and styles.')
                    ->schema([
                        ActionsComponent::make([
                            $this->demoAction('disabled_solid', 'Solid disabled')->disabled(),
                            $this->demoAction('disabled_outlined', 'Outlined disabled')->outlined()->disabled(),
                            $this->demoAction('disabled_link', 'Link disabled')->link()->disabled(),
                            $this->demoAction('disabled_icon', 'Icon disabled')
                                ->icon(Heroicon::OutlinedTrash)
                                ->iconButton()
                                ->color('danger')
                                ->disabled(),
                        ]),
                    ]),
            ]);
    }

    protected function modalsTab(): Tab
    {
        return Tab::make('Modals & overlays')
            ->icon(Heroicon::OutlinedRectangleStack)
            ->schema([
                Section::make('Modals')
                    ->description('Centred modal, slide-over, and a confirmation modal for destructive flows.')
                    ->schema([
                        ActionsComponent::make([
                            Action::make('open_modal')
                                ->label('Open modal')
                                ->icon(Heroicon::OutlinedDocumentText)
                                ->modalHeading('Modal heading')
                                ->modalDescription('A standard centred modal with submit and cancel actions in the footer.')
                                ->modalSubmitActionLabel('Confirm')
                                ->action(fn () => $this->fireDemoNotification('Modal submitted', 'success')),

                            Action::make('open_slide_over')
                                ->label('Open slide-over')
                                ->icon(Heroicon::OutlinedArrowsRightLeft)
                                ->slideOver()
                                ->modalHeading('Slide-over heading')
                                ->modalDescription('A side-anchored modal — useful for editing one record without leaving the listing context.')
                                ->action(fn () => $this->fireDemoNotification('Slide-over submitted', 'success')),

                            Action::make('confirm_destructive')
                                ->label('Delete (with confirmation)')
                                ->icon(Heroicon::OutlinedTrash)
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading('Delete this item?')
                                ->modalDescription('This action cannot be undone. The catalogue won\'t actually delete anything — it just fires a notification.')
                                ->modalSubmitActionLabel('Yes, delete')
                                ->action(fn () => $this->fireDemoNotification('Deleted (demo)', 'danger')),
                        ]),
                    ]),
            ]);
    }

    protected function notificationsTab(): Tab
    {
        return Tab::make('Notifications')
            ->icon(Heroicon::OutlinedBell)
            ->schema([
                Section::make('Toast notifications')
                    ->description('Each variant fires a Filament Notification with the matching status.')
                    ->schema([
                        ActionsComponent::make([
                            Action::make('notify_success')
                                ->label('Success')
                                ->color('success')
                                ->icon(Heroicon::OutlinedCheckCircle)
                                ->action(fn () => Notification::make()
                                    ->title('Success')
                                    ->body('Your changes were saved.')
                                    ->success()
                                    ->send()),

                            Action::make('notify_info')
                                ->label('Info')
                                ->color('info')
                                ->icon(Heroicon::OutlinedInformationCircle)
                                ->action(fn () => Notification::make()
                                    ->title('Info')
                                    ->body('A new release is available.')
                                    ->info()
                                    ->send()),

                            Action::make('notify_warning')
                                ->label('Warning')
                                ->color('warning')
                                ->icon(Heroicon::OutlinedExclamationTriangle)
                                ->action(fn () => Notification::make()
                                    ->title('Warning')
                                    ->body('Your session will expire in 5 minutes.')
                                    ->warning()
                                    ->send()),

                            Action::make('notify_danger')
                                ->label('Danger')
                                ->color('danger')
                                ->icon(Heroicon::OutlinedXCircle)
                                ->action(fn () => Notification::make()
                                    ->title('Danger')
                                    ->body('Could not connect to the server.')
                                    ->danger()
                                    ->send()),
                        ]),
                    ]),
            ]);
    }

    protected function groupsTab(): Tab
    {
        return Tab::make('Groups')
            ->icon(Heroicon::OutlinedListBullet)
            ->schema([
                Section::make('Action group (dropdown)')
                    ->description('A button that opens a dropdown of related actions. Useful for row-level menus on tables.')
                    ->schema([
                        ActionsComponent::make([
                            ActionGroup::make([
                                $this->demoAction('group_view', 'View')->icon(Heroicon::OutlinedEye),
                                $this->demoAction('group_edit', 'Edit')->icon(Heroicon::OutlinedPencil),
                                $this->demoAction('group_archive', 'Archive')->icon(Heroicon::OutlinedArchiveBox),
                                Action::make('group_delete')
                                    ->label('Delete')
                                    ->icon(Heroicon::OutlinedTrash)
                                    ->color('danger')
                                    ->requiresConfirmation()
                                    ->action(fn () => $this->fireDemoNotification('Deleted (demo)', 'danger')),
                            ]),
                        ]),
                        Text::make('The default appearance is a kebab/three-dots button. ActionGroup also supports tooltip, icon overrides, and grouping items into nested sub-groups.'),
                    ]),

                Section::make('Inline action row')
                    ->description('Several actions side-by-side, the most common pattern at the top of a resource page.')
                    ->schema([
                        ActionsComponent::make([
                            $this->demoAction('row_create', 'Create')
                                ->icon(Heroicon::OutlinedPlus)
                                ->color('primary'),
                            $this->demoAction('row_export', 'Export')
                                ->icon(Heroicon::OutlinedArrowDownTray)
                                ->color('gray')
                                ->outlined(),
                            $this->demoAction('row_filter', 'Filter')
                                ->icon(Heroicon::OutlinedFunnel)
                                ->color('gray')
                                ->outlined(),
                        ]),
                    ]),
            ]);
    }

    /**
     * Demo action factory — every example button does the same thing (fires
     * a success notification) so the click feedback is consistent.
     */
    protected function demoAction(string $name, string $label): Action
    {
        return Action::make($name)
            ->label($label)
            ->action(fn () => $this->fireDemoNotification("{$label} clicked", 'success'));
    }

    protected function fireDemoNotification(string $title, string $status): void
    {
        $notification = Notification::make()
            ->title($title)
            ->body('Demo flow — nothing was saved.');

        match ($status) {
            'success' => $notification->success(),
            'info' => $notification->info(),
            'warning' => $notification->warning(),
            'danger' => $notification->danger(),
            default => null,
        };

        $notification->send();
    }
}
