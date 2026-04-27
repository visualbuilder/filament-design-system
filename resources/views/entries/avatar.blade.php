{{--
  Filament ViewEntry wrapper for the initials avatar — mirror of the
  columns/avatar wrapper but for infolist entries. Use:

      Filament\Infolists\Components\ViewEntry::make('avatar')
          ->state('Lee Evans')
          ->view('filament-design-system::entries.avatar')
          ->extraAttributes(['data-avatar-size' => 'xl'])
--}}
@php
    $seed = (string) ($getState() ?? '');
    /** @var \Filament\Schemas\Components\Component|null $entry */
    $entry = $component ?? null;
    $size = $entry?->getExtraAttributes()['data-avatar-size'] ?? 'lg';
@endphp

<x-filament-design-system::avatar :seed="$seed" :size="$size" />
