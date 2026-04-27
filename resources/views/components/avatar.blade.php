{{--
  Initials avatar — text + rounded background, fully self-contained (no
  external requests, CSP-safe). Replaces previous dicebear API usage in the
  catalogue and is exposed as a reusable component for hosts that need an
  avatar fallback elsewhere.

  Usage:
    <x-filament-design-system::avatar :seed="$user->name" />
    <x-filament-design-system::avatar :seed="$user->name" size="lg" />

  Props:
    seed  — string the initials and hue derive from (typically a name)
    size  — xs | sm | md | lg | xl   (default: md)
--}}
@props([
    'seed' => '',
    'size' => 'md',
])

@php
    $sizes = [
        'xs' => ['box' => '1.5rem', 'font' => '0.625rem'],
        'sm' => ['box' => '2rem',   'font' => '0.75rem'],
        'md' => ['box' => '2.5rem', 'font' => '0.875rem'],
        'lg' => ['box' => '3rem',   'font' => '1rem'],
        'xl' => ['box' => '4rem',   'font' => '1.5rem'],
    ];
    $dimensions = $sizes[$size] ?? $sizes['md'];

    // Deterministic hue from the seed so the same name always gets the same colour.
    $hue = abs(crc32((string) $seed)) % 360;

    // Initials: first letter of the first word + first letter of the last word.
    $parts = preg_split('/\s+/', trim((string) $seed)) ?: [];
    $first = $parts[0] ?? '';
    $last = end($parts) ?: $first;
    $initials = mb_strtoupper(
        mb_substr($first, 0, 1)
        . ($last !== $first ? mb_substr((string) $last, 0, 1) : '')
    );
@endphp

<span
    {{ $attributes->class(['fi-ds-avatar'])->merge([
        'style' => "display:inline-flex;align-items:center;justify-content:center;"
                 . "width:{$dimensions['box']};height:{$dimensions['box']};"
                 . "border-radius:9999px;font-weight:600;color:#fff;"
                 . "font-size:{$dimensions['font']};line-height:1;letter-spacing:0.025em;"
                 . "background-color:hsl({$hue} 60% 50%);"
                 . "user-select:none;flex-shrink:0;",
    ]) }}
    aria-hidden="true"
>{{ $initials }}</span>
