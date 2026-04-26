<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use BladeUI\Icons\Factory as IconFactory;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Icons extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $title = 'Icons';

    protected static ?int $navigationSort = 45;

    protected string $view = 'filament-design-system::pages.icons';

    public ?string $activeTab = null;

    public function setActiveTab(?string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * @return array<int, array{key: ?string, label: string, count: int}>
     */
    public function getTabsView(): array
    {
        $manifest = cache()->remember(
            'design-system.icons.manifest',
            now()->addHour(),
            fn (): array => $this->buildManifest(),
        );

        $counts = collect($manifest)->countBy('set');
        $labels = $this->setLabels();

        $tabs = [['key' => null, 'label' => 'All', 'count' => count($manifest)]];

        foreach ($labels as $key => $label) {
            $count = $counts[$key] ?? 0;
            if ($count === 0) {
                continue;
            }
            $tabs[] = ['key' => $key, 'label' => $label, 'count' => $count];
        }

        return $tabs;
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (
                int $page,
                int $recordsPerPage,
                ?string $search,
            ): LengthAwarePaginator => $this->icons($page, $recordsPerPage, $search))
            ->recordTitleAttribute('identifier')
            ->columns([
                // Stack wraps the columns vertically so contentGrid() can render
                // each row as a card. Without Stack, the table falls back to the
                // standard horizontal table layout.
                Stack::make([
                    TextColumn::make('preview')
                        ->label('Preview')
                        ->alignCenter()
                        // Returns an Htmlable (BladeUI\Icons\Svg) so Filament skips its HTML
                        // sanitiser — it would otherwise strip the <svg> tag and attributes.
                        ->formatStateUsing(fn (string $state) => svg($state, 'ds-icon-preview-svg')),

                    TextColumn::make('identifier')
                        ->label('Identifier')
                        ->alignCenter()
                        ->copyable()
                        ->copyMessage('Copied')
                        ->searchable()
                        ->extraAttributes([
                            'style' => 'font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.75rem; word-break: break-all;',
                        ]),

                    TextColumn::make('set_label')
                        ->label('Set')
                        ->alignCenter()
                        ->badge()
                        ->color(fn (array $record): string => match ($record['set']) {
                            'heroicons' => 'primary',
                            'fontawesome-solid' => 'success',
                            'fontawesome-regular' => 'info',
                            'fontawesome-brands' => 'warning',
                            'filament' => 'gray',
                            'default' => 'danger',
                            default => 'gray',
                        }),
                ])->alignCenter(),
            ])
            ->contentGrid([
                'default' => 2,
                'sm' => 3,
                'md' => 4,
                'lg' => 6,
                'xl' => 8,
            ])
            ->defaultPaginationPageOption(96)
            ->paginated([24, 48, 96]);
    }

    protected function icons(int $page, int $perPage, ?string $search): LengthAwarePaginator
    {
        $cached = cache()->remember(
            'design-system.icons.manifest',
            now()->addHour(),
            fn (): array => $this->buildManifest(),
        );

        $rows = collect($cached);

        if ($this->activeTab !== null) {
            $rows = $rows->filter(fn (array $r) => $r['set'] === $this->activeTab);
        }

        if (filled($search)) {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(fn (array $r) => str_contains(mb_strtolower($r['identifier']), $needle));
        }

        $total = $rows->count();

        $slice = $rows
            ->slice(($page - 1) * $perPage, $perPage)
            ->mapWithKeys(fn (array $r) => [$r['identifier'] => $r])
            ->all();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
        );
    }

    /**
     * Walk every blade-icons set, glob its SVG paths, and produce a flat list
     * of icon records. Cached for an hour because it touches the filesystem.
     *
     * @return array<int, array<string, string>>
     */
    protected function buildManifest(): array
    {
        $factory = app(IconFactory::class);
        $labels = $this->setLabels();
        $manifest = [];

        foreach ($factory->all() as $setName => $options) {
            $prefix = $options['prefix'] ?? '';
            $label = $labels[$setName] ?? Str::title(str_replace('-', ' ', $setName));

            foreach ((array) ($options['paths'] ?? []) as $base) {
                if (! is_string($base) || ! is_dir($base)) {
                    continue;
                }

                foreach (glob($base . '/*.svg') ?: [] as $file) {
                    $name = basename($file, '.svg');
                    $identifier = $prefix === '' ? $name : "{$prefix}-{$name}";

                    $manifest[] = [
                        // 'preview' duplicates the identifier so the TextColumn::make('preview')
                        // resolves a non-blank state. Filament's TextColumn renders a placeholder
                        // and skips formatStateUsing entirely when state is blank.
                        'preview' => $identifier,
                        'identifier' => $identifier,
                        'name' => $name,
                        'set' => $setName,
                        'set_label' => $label,
                    ];
                }
            }
        }

        usort($manifest, fn (array $a, array $b): int => strcmp($a['identifier'], $b['identifier']));

        return $manifest;
    }

    /**
     * @return array<string, string>
     */
    protected function setLabels(): array
    {
        return [
            'default' => 'Custom (resources/svg)',
            'heroicons' => 'Heroicons',
            'filament' => 'Filament internal',
            'fontawesome-solid' => 'FontAwesome (Solid)',
            'fontawesome-regular' => 'FontAwesome (Regular)',
            'fontawesome-brands' => 'FontAwesome (Brands)',
        ];
    }
}
