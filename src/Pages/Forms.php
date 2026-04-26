<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Forms extends Page
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $title = 'Forms';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament-design-system::pages.forms';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'select_basic' => 'option_b',
            'radio' => 'two',
            'toggle' => true,
            'toggle_buttons' => 'review',
            'date' => '2026-04-26',
            'colour' => '#664dff',
            'tags' => ['design', 'system', 'filament'],
            'rich_editor' => '<p>The quick <strong>brown fox</strong> jumps over the lazy dog.</p>',
            'markdown' => "## Heading\n\n- One\n- Two\n- Three",
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Form components')
                    ->contained(false)
                    ->tabs([
                        $this->inputsTab(),
                        $this->selectionsTab(),
                        $this->dateAndNumericTab(),
                        $this->tagsAndFilesTab(),
                        $this->compositeTab(),
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

    protected function inputsTab(): Tab
    {
        return Tab::make('Inputs')
            ->icon(Heroicon::OutlinedPencil)
            ->schema([
                Section::make('Text input')
                    ->description('TextInput — the workhorse single-line field.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('text_default')
                            ->label('Default')
                            ->placeholder('Type something…'),

                        TextInput::make('text_required')
                            ->label('Required with helper')
                            ->required()
                            ->helperText('Helper text gives context just below the field.'),

                        TextInput::make('text_disabled')
                            ->label('Disabled')
                            ->disabled()
                            ->default('Read-only value'),

                        TextInput::make('text_with_affixes')
                            ->label('Prefix & suffix')
                            ->prefix('https://')
                            ->suffix('.com'),

                        TextInput::make('text_password')
                            ->label('Password')
                            ->password()
                            ->revealable(),

                        TextInput::make('text_email')
                            ->label('Email')
                            ->email()
                            ->prefixIcon(Heroicon::OutlinedEnvelope),
                    ]),

                Section::make('Multi-line')
                    ->columns(2)
                    ->schema([
                        Textarea::make('textarea_default')
                            ->label('Textarea')
                            ->rows(4)
                            ->placeholder('Multi-line input.'),

                        Textarea::make('textarea_autosize')
                            ->label('Autosize textarea')
                            ->autosize(),
                    ]),

                Section::make('Editors')
                    ->description('Rich and Markdown editors. Toolbars use the panel theme tokens.')
                    ->schema([
                        RichEditor::make('rich_editor')
                            ->label('Rich editor')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'codeBlock']),

                        MarkdownEditor::make('markdown')
                            ->label('Markdown editor'),
                    ]),
            ]);
    }

    protected function selectionsTab(): Tab
    {
        return Tab::make('Selections')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->schema([
                Section::make('Select')
                    ->columns(2)
                    ->schema([
                        Select::make('select_basic')
                            ->label('Basic select')
                            ->options($this->fruitOptions()),

                        Select::make('select_searchable')
                            ->label('Searchable')
                            ->searchable()
                            ->options($this->fruitOptions()),

                        Select::make('select_multi')
                            ->label('Multi-select')
                            ->multiple()
                            ->preload()
                            ->options($this->fruitOptions()),

                        Select::make('select_native')
                            ->label('Native (browser select)')
                            ->native()
                            ->options($this->fruitOptions()),
                    ]),

                Section::make('Radio & Checkbox')
                    ->columns(2)
                    ->schema([
                        Radio::make('radio')
                            ->label('Radio')
                            ->options(['one' => 'Option one', 'two' => 'Option two', 'three' => 'Option three']),

                        Radio::make('radio_inline')
                            ->label('Inline radio')
                            ->inline()
                            ->inlineLabel(false)
                            ->options(['s' => 'Small', 'm' => 'Medium', 'l' => 'Large']),

                        Checkbox::make('checkbox_single')
                            ->label('Single checkbox')
                            ->helperText('I agree to the terms.'),

                        CheckboxList::make('checkbox_list')
                            ->label('Checkbox list')
                            ->columns(2)
                            ->options($this->fruitOptions()),
                    ]),

                Section::make('Toggle')
                    ->columns(2)
                    ->schema([
                        Toggle::make('toggle')
                            ->label('Toggle')
                            ->inline(false)
                            ->onIcon(Heroicon::Check)
                            ->offIcon(Heroicon::XMark),

                        ToggleButtons::make('toggle_buttons')
                            ->label('Toggle buttons')
                            ->options([
                                'draft' => 'Draft',
                                'review' => 'In review',
                                'published' => 'Published',
                            ])
                            ->colors([
                                'draft' => 'gray',
                                'review' => 'warning',
                                'published' => 'success',
                            ])
                            ->icons([
                                'draft' => Heroicon::OutlinedDocument,
                                'review' => Heroicon::OutlinedEye,
                                'published' => Heroicon::OutlinedCheckCircle,
                            ])
                            ->inline(),
                    ]),
            ]);
    }

    protected function dateAndNumericTab(): Tab
    {
        return Tab::make('Date & numeric')
            ->icon(Heroicon::OutlinedCalendar)
            ->schema([
                Section::make('Date & time')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('date')->label('Date'),
                        DateTimePicker::make('datetime')->label('Date & time'),
                        TimePicker::make('time')->label('Time'),
                    ]),

                Section::make('Numeric & colour')
                    ->columns(3)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Currency')
                            ->numeric()
                            ->prefix('£')
                            ->step(0.01),

                        TextInput::make('quantity')
                            ->label('Stepper')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),

                        ColorPicker::make('colour')
                            ->label('Colour picker'),
                    ]),
            ]);
    }

    protected function tagsAndFilesTab(): Tab
    {
        return Tab::make('Tags & files')
            ->icon(Heroicon::OutlinedTag)
            ->schema([
                Section::make('Tags & key-value')
                    ->columns(2)
                    ->schema([
                        TagsInput::make('tags')
                            ->label('Tags')
                            ->placeholder('Add tag…'),

                        KeyValue::make('metadata')
                            ->label('Key-value pairs')
                            ->keyLabel('Key')
                            ->valueLabel('Value'),
                    ]),

                Section::make('File upload')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('Avatar')
                            ->avatar()
                            ->image(),

                        FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->maxFiles(3),
                    ]),
            ]);
    }

    protected function compositeTab(): Tab
    {
        return Tab::make('Composite')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->schema([
                Section::make('Repeater')
                    ->description('A list of identical sub-forms.')
                    ->schema([
                        Repeater::make('contacts')
                            ->label('Contacts')
                            ->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('email')->email(),
                                Select::make('role')
                                    ->options(['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer']),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(1),
                    ]),

                Section::make('Builder')
                    ->description('A list of polymorphic blocks. Each block has its own schema.')
                    ->schema([
                        Builder::make('content')
                            ->label('Content blocks')
                            ->blocks([
                                Block::make('heading')
                                    ->icon(Heroicon::OutlinedH1)
                                    ->schema([
                                        TextInput::make('text')->required(),
                                        Select::make('level')
                                            ->options(['h2' => 'H2', 'h3' => 'H3'])
                                            ->default('h2'),
                                    ]),

                                Block::make('paragraph')
                                    ->icon(Heroicon::OutlinedBars3BottomLeft)
                                    ->schema([
                                        Textarea::make('body')->rows(3)->required(),
                                    ]),
                            ])
                            ->collapsible(),
                    ]),
            ]);
    }

    /**
     * @return array<string,string>
     */
    protected function fruitOptions(): array
    {
        return [
            'option_a' => 'Apple',
            'option_b' => 'Banana',
            'option_c' => 'Cherry',
            'option_d' => 'Damson',
            'option_e' => 'Elderberry',
        ];
    }
}
