{{--
  Filament ViewColumn wrapper for the initials avatar. Use it like:

      Tables\Columns\ViewColumn::make('avatar')
          ->state(fn (array $record): string => $record['name'])
          ->view('filament-design-system::columns.avatar')

  $getState() yields the seed string supplied via ->state(); we read an
  optional 'data-avatar-size' from extraAttributes so column setup can pick
  xs|sm|md|lg|xl per use site (default md).
--}}
@php
    $seed = (string) ($getState() ?? '');
    $size = $column->getExtraAttributes()['data-avatar-size'] ?? 'md';
@endphp

<x-filament-design-system::avatar :seed="$seed" :size="$size" />
