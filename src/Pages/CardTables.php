<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CardTables extends Page implements HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $title = 'Card-style tables';

    protected static ?int $navigationSort = 41;

    protected string $view = 'filament-design-system::pages.card-tables';

    public ?array $data = ['layout' => 'two'];

    public function mount(): void
    {
        $this->form->fill(['layout' => 'two']);
    }

    /**
     * Rebuild the cached table when the layout toggle changes.
     *
     * Filament caches the table via bootedInteractsWithTable() before Livewire
     * has applied incoming property updates, so the first click reads the prior
     * layout. This hook re-runs the schema after the new value lands.
     */
    public function updatedData(): void
    {
        $this->table = $this->table($this->makeTable());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ToggleButtons::make('layout')
                    ->label('Grid density')
                    ->inline()
                    ->options([
                        'one' => 'Full width',
                        'two' => 'Two columns',
                        'three' => 'Three columns',
                    ])
                    ->icons([
                        'one' => Heroicon::OutlinedSquare2Stack,
                        'two' => Heroicon::OutlinedRectangleGroup,
                        'three' => Heroicon::OutlinedSquares2x2,
                    ])
                    ->live(),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $layout = $this->data['layout'] ?? 'two';

        return $table
            ->records(fn (): Collection => $this->people())
            ->recordTitleAttribute('name')
            ->columns($this->columnsFor($layout))
            ->recordActions($layout === 'one' ? $this->fullWidthActions() : [])
            ->contentGrid(match ($layout) {
                'one' => ['default' => 1],
                'three' => ['default' => 1, 'md' => 2, 'lg' => 3],
                default => ['default' => 1, 'md' => 2],
            })
            ->paginated(false);
    }

    /**
     * @return array<int, \Filament\Tables\Columns\Column|\Filament\Tables\Columns\Layout\Component>
     */
    protected function columnsFor(string $layout): array
    {
        if ($layout === 'one') {
            // Full-width cards use Split — horizontal layout with avatar + textual
            // content on the left, status badge to the right, and a row action.
            return [
                Split::make([
                    ViewColumn::make('avatar')
                        ->label('Avatar')
                        ->grow(false)
                        ->state(fn (array $record): string => (string) $record['name'])
                        ->view('filament-design-system::columns.avatar')
                        ->extraAttributes(['data-avatar-size' => 'lg']),

                    Stack::make([
                        TextColumn::make('name')
                            ->label('Name')
                            ->weight('semibold')
                            ->searchable(),
                        TextColumn::make('role')
                            ->label('Role')
                            ->color('gray')
                            ->size('sm'),
                    ])->space(1),

                    TextColumn::make('status')
                        ->label('Status')
                        ->badge()
                        ->grow(false)
                        ->color(fn (array $record): string => match ($record['status']) {
                            'available' => 'success',
                            'busy' => 'warning',
                            'away' => 'gray',
                            default => 'gray',
                        })
                        ->icon(fn (array $record): string|\BackedEnum => match ($record['status']) {
                            'available' => Heroicon::OutlinedCheckCircle,
                            'busy' => Heroicon::OutlinedClock,
                            'away' => Heroicon::OutlinedMoon,
                            default => Heroicon::OutlinedQuestionMarkCircle,
                        }),
                ])->from('md'),
            ];
        }

        // Two- and three-column cards use Stack — content centred vertically inside each card.
        return [
            Stack::make([
                ViewColumn::make('avatar')
                    ->label('Avatar')
                    ->alignCenter()
                    ->state(fn (array $record): string => (string) $record['name'])
                    ->view('filament-design-system::columns.avatar')
                    ->extraAttributes(['data-avatar-size' => 'xl']),

                TextColumn::make('name')
                    ->label('Name')
                    ->weight('semibold')
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->alignCenter()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (array $record): string => match ($record['status']) {
                        'available' => 'success',
                        'busy' => 'warning',
                        'away' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (array $record): string|\BackedEnum => match ($record['status']) {
                        'available' => Heroicon::OutlinedCheckCircle,
                        'busy' => Heroicon::OutlinedClock,
                        'away' => Heroicon::OutlinedMoon,
                        default => Heroicon::OutlinedQuestionMarkCircle,
                    }),
            ])->alignCenter(),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected function fullWidthActions(): array
    {
        return [
            Action::make('message')
                ->label('Message')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('primary')
                ->action(fn (array $record) => Notification::make()
                    ->title("Message {$record['name']} (demo)")
                    ->body('Demo flow — nothing was sent.')
                    ->success()
                    ->send()),
        ];
    }

    protected function people(): Collection
    {
        return collect([
            ['Lee Evans', 'Lead engineer', 'available'],
            ['Sam Carter', 'Product designer', 'busy'],
            ['Priya Rao', 'Data scientist', 'available'],
            ['Mateo Fernández', 'Backend engineer', 'away'],
            ['Hannah Kim', 'Frontend engineer', 'available'],
            ['Idris Mahmood', 'QA lead', 'busy'],
            ['Saskia van der Berg', 'Customer success', 'available'],
            ['Yumi Tanaka', 'Operations', 'away'],
            ['Olu Adebayo', 'Account manager', 'available'],
        ])->map(fn (array $row, int $i): array => [
            'id' => $i + 1,
            'name' => $row[0],
            'role' => $row[1],
            'status' => $row[2],
            'avatar_url' => null,
        ])->mapWithKeys(fn (array $r) => [$r['id'] => $r]);
    }
}
