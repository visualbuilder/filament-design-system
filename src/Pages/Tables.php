<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Tables extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $title = 'Tables';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament-design-system::pages.tables';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (
                int $page,
                int $recordsPerPage,
                ?string $sortColumn,
                ?string $sortDirection,
                ?string $search,
                array $filters,
            ): LengthAwarePaginator => $this->orders($page, $recordsPerPage, $sortColumn, $sortDirection, $search, $filters))
            ->recordTitleAttribute('reference')
            ->columns([
                ViewColumn::make('avatar')
                    ->label('Avatar')
                    ->state(fn (array $record): string => (string) $record['customer_name'])
                    ->view('filament-design-system::columns.avatar')
                    ->extraAttributes(['data-avatar-size' => 'lg']),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->weight('semibold'),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (array $record): string => $record['customer_email'])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (array $record): string => match ($record['status']) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (array $record): string|\BackedEnum => match ($record['status']) {
                        'draft' => Heroicon::OutlinedDocument,
                        'pending' => Heroicon::OutlinedClock,
                        'completed' => Heroicon::OutlinedCheckCircle,
                        'cancelled' => Heroicon::OutlinedXCircle,
                        default => Heroicon::OutlinedQuestionMarkCircle,
                    }),

                IconColumn::make('is_priority')
                    ->label('Priority')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedStar)
                    ->falseIcon(Heroicon::Star)
                    ->trueColor('warning')
                    ->falseColor('gray'),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('GBP')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('New order')
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('primary')
                    ->modalHeading('Create order')
                    ->modalDescription('Demo flow — nothing is persisted.')
                    ->schema([
                        TextInput::make('reference')->required()->prefix('NB-'),
                        TextInput::make('customer_name')->label('Customer')->required(),
                        Select::make('status')
                            ->options(['draft' => 'Draft', 'pending' => 'Pending'])
                            ->default('draft')
                            ->required(),
                    ])
                    ->action(fn () => $this->demoNotify('New order created (demo)', 'success')),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedEye)
                    ->modalHeading(fn (array $record): string => "Order {$record['reference']}")
                    ->modalDescription(fn (array $record): string => "Customer: {$record['customer_name']}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Action::make('edit')
                    ->icon(Heroicon::OutlinedPencil)
                    ->modalHeading(fn (array $record): string => "Edit {$record['reference']}")
                    ->schema([
                        TextInput::make('reference')->required(),
                        TextInput::make('customer_name')->label('Customer')->required(),
                    ])
                    ->fillForm(fn (array $record): array => [
                        'reference' => $record['reference'],
                        'customer_name' => $record['customer_name'],
                    ])
                    ->action(fn (array $record) => $this->demoNotify("Updated {$record['reference']} (demo)", 'success')),

                Action::make('delete')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (array $record): string => "Delete {$record['reference']}?")
                    ->modalDescription('Demo flow — nothing is persisted.')
                    ->modalSubmitActionLabel('Yes, delete')
                    ->action(fn (array $record) => $this->demoNotify("Deleted {$record['reference']} (demo)", 'danger')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_reviewed')
                        ->label('Mark as reviewed')
                        ->icon(Heroicon::OutlinedCheck)
                        ->color('success')
                        ->action(fn (Collection $records) => $this->demoNotify("Marked {$records->count()} order(s) reviewed (demo)", 'success')),

                    BulkAction::make('export')
                        ->label('Export selected')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('gray')
                        ->action(fn (Collection $records) => $this->demoNotify("Exported {$records->count()} order(s) (demo)", 'info')),
                ]),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25]);
    }

    /**
     * Demo dataset. Returns a LengthAwarePaginator keyed by record id (the docs
     * require stable keys so Livewire can diff selections cleanly).
     */
    protected function orders(
        int $page,
        int $perPage,
        ?string $sortColumn,
        ?string $sortDirection,
        ?string $search,
        array $filters,
    ): LengthAwarePaginator {
        $rows = collect($this->seedOrders());

        $statusFilter = $filters['status']['values'] ?? $filters['status']['value'] ?? null;
        if (! empty($statusFilter)) {
            $statusFilter = (array) $statusFilter;
            $rows = $rows->filter(fn (array $r) => in_array($r['status'], $statusFilter, true));
        }

        if (filled($search)) {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(fn (array $r) => str_contains(mb_strtolower($r['reference']), $needle)
                || str_contains(mb_strtolower($r['customer_name']), $needle)
                || str_contains(mb_strtolower($r['customer_email']), $needle)
            );
        }

        if (filled($sortColumn)) {
            $rows = $rows->sortBy($sortColumn, SORT_REGULAR, $sortDirection === 'desc');
        }

        $total = $rows->count();

        $slice = $rows
            ->slice(($page - 1) * $perPage, $perPage)
            ->mapWithKeys(fn (array $r) => [$r['id'] => $r])
            ->all();

        return new LengthAwarePaginator($slice, $total, $perPage, $page);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function seedOrders(): array
    {
        $statuses = ['draft', 'pending', 'completed', 'cancelled'];

        return collect([
            ['Liu Wei', 'liu.wei@example.test'],
            ['Amara Okafor', 'amara.okafor@example.test'],
            ['Sebastian Nilsson', 'sebastian.nilsson@example.test'],
            ['Priya Rao', 'priya.rao@example.test'],
            ['Mateo Fernández', 'mateo.fernandez@example.test'],
            ['Hannah Kim', 'hannah.kim@example.test'],
            ['Idris Mahmood', 'idris.mahmood@example.test'],
            ['Saskia van der Berg', 'saskia.vanderberg@example.test'],
            ['Tomás Ribeiro', 'tomas.ribeiro@example.test'],
            ['Yumi Tanaka', 'yumi.tanaka@example.test'],
            ['Olu Adebayo', 'olu.adebayo@example.test'],
            ['Beatrix Holzer', 'beatrix.holzer@example.test'],
        ])->map(function (array $person, int $i) use ($statuses) {
            return [
                'id' => $i + 1,
                'reference' => 'NB-' . str_pad((string) (1000 + $i + 1), 4, '0', STR_PAD_LEFT),
                'customer_name' => $person[0],
                'customer_email' => $person[1],
                'avatar_url' => null,
                'status' => $statuses[$i % count($statuses)],
                'is_priority' => $i % 3 === 0,
                'total' => round(50 + ($i * 37.5), 2),
                'created_at' => now()->subDays($i)->subHours($i),
            ];
        })->all();
    }

    protected function demoNotify(string $title, string $variant): void
    {
        $n = Notification::make()->title($title)->body('Demo flow — nothing was saved.');

        match ($variant) {
            'success' => $n->success(),
            'info' => $n->info(),
            'warning' => $n->warning(),
            'danger' => $n->danger(),
            default => null,
        };

        $n->send();
    }
}
