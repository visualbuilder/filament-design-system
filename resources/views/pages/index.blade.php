<x-filament-panels::page>
    <style>
        .ds-page { color: inherit; }

        .ds-section { margin-bottom: 3rem; }
        .ds-section + .ds-section { margin-top: 3rem; }

        .ds-section__head { padding-bottom: 0.5rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(127,127,127,0.18); }
        .ds-section__title { font-size: 1.125rem; font-weight: 600; }
        .ds-section__sub { font-size: 0.8125rem; opacity: 0.7; margin-top: 0.25rem; }
        .ds-section__group-label { font-size: 0.8125rem; font-weight: 500; opacity: 0.85; margin: 1rem 0 0.5rem; text-transform: capitalize; }

        /* Typography rows */
        .ds-type-row { padding: 0.25rem 0; line-height: 1.15; }

        /* Swatch grid */
        .ds-swatch-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 0.75rem; }
        .ds-swatch { display: flex; flex-direction: column; gap: 0.375rem; }
        .ds-swatch__tile { aspect-ratio: 1 / 1; width: 100%; border-radius: 0.375rem; box-shadow: inset 0 0 0 1px rgba(127,127,127,0.18); }
        .ds-swatch__meta { display: flex; flex-direction: column; gap: 0.0625rem; }
        .ds-swatch__label { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.6875rem; opacity: 0.85; }
        .ds-swatch__hex { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.6875rem; opacity: 0.55; }

        /* Spacing + radius previews */
        .ds-two-col { display: grid; grid-template-columns: 1fr; gap: 2rem; }
        @media (min-width: 640px) { .ds-two-col { grid-template-columns: 1fr 1fr; } }

        .ds-space-row { display: flex; align-items: center; gap: 0.75rem; padding: 0.125rem 0; }
        .ds-space-row__tag { flex: 0 0 2.5rem; font-family: ui-monospace, monospace; font-size: 0.6875rem; opacity: 0.6; text-align: right; }
        .ds-space-row__bar { height: 0.75rem; background-color: var(--color-primary-500); border-radius: 0.125rem; }
        .ds-space-row__num { font-family: ui-monospace, monospace; font-size: 0.6875rem; opacity: 0.55; }

        .ds-radius-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
        .ds-radius-cell { display: flex; flex-direction: column; align-items: center; gap: 0.375rem; }
        .ds-radius-cell__shape { width: 3rem; height: 3rem; background-color: var(--color-primary-600); }
        .ds-radius-cell__name { font-family: ui-monospace, monospace; font-size: 0.6875rem; opacity: 0.55; }

        /* Token table */
        .ds-tokens { width: 100%; border-collapse: collapse; font-size: 0.8125rem; border: 1px solid rgba(127,127,127,0.18); border-radius: 0.375rem; overflow: hidden; }
        .ds-tokens thead { background: rgba(127,127,127,0.06); }
        .ds-tokens th, .ds-tokens td { text-align: left; padding: 0.4rem 0.75rem; border-bottom: 1px solid rgba(127,127,127,0.08); }
        .ds-tokens tr:last-child td { border-bottom: 0; }
        .ds-tokens td { font-family: ui-monospace, monospace; font-size: 0.75rem; }
        .ds-tokens td:nth-child(3) { opacity: 0.7; }
        .ds-tokens__col-swatch { width: 1.5rem; padding-right: 0 !important; }
        .ds-tokens__swatch { display: inline-block; width: 1rem; height: 1rem; border-radius: 0.1875rem; box-shadow: inset 0 0 0 1px rgba(127,127,127,0.22); vertical-align: -0.1875rem; }
    </style>

    <div class="ds-page">
        {{-- Welcome hero — replicates the "Welcome / <name>" pattern from
             the designer's reference. Hosts that ship their own illustration
             (e.g. resources/svg/wave.svg) pass its blade-icon name as the
             `icon` prop. Falls back to a Heroicon if that prop is omitted. --}}
        <section class="ds-section">
            <header class="ds-section__head">
                <div class="ds-section__title">Welcome hero</div>
                <div class="ds-section__sub">
                    Circular illustration badge + light pink greeting + bolder name.
                </div>
            </header>

            <x-filament-design-system::welcome
                icon="wave"
                greeting="Welcome"
                name="Zoe Chadwick"
            />
        </section>

        {{-- Typography --}}
        <section class="ds-section">
            <header class="ds-section__head">
                <div class="ds-section__title">Typography</div>
                <div class="ds-section__sub">
                    Family: <code>{{ $panel_font }}</code> ·
                    Heading weight: <code>{{ $typography['weight']['heading'] }}</code>
                </div>
            </header>

            @foreach (['5xl' => 'Heading at 5xl', '4xl' => 'Heading at 4xl', '3xl' => 'Heading at 3xl', '2xl' => 'Heading at 2xl', 'xl' => 'Heading at xl', 'lg' => 'Body lead at lg', 'base' => 'Body copy at base size — quick brown fox.', 'sm' => 'Helper text at sm', 'xs' => 'Caption at xs'] as $size => $sample)
                @php
                    $isHeading = in_array($size, ['5xl', '4xl', '3xl', '2xl'], true);
                    $weight = $isHeading ? 'var(--type-weight-heading)' : 'var(--type-weight-regular)';
                @endphp
                <div class="ds-type-row" style="font-size: var(--type-size-{{ $size }}); font-weight: {{ $weight }};">{{ $sample }}</div>
            @endforeach
        </section>

        {{-- Colour sections — config-driven from catalogue.colour_sections --}}
        @foreach ($colour_sections as $section)
            @php
                $palettes = collect($section['palettes'] ?? [])
                    ->filter(fn ($key) => isset($colors[$key]))
                    ->values();
            @endphp
            @continue($palettes->isEmpty())

            <section class="ds-section">
                <header class="ds-section__head">
                    <div class="ds-section__title">{{ $section['title'] }}</div>
                    @if (! empty($section['description']))
                        <div class="ds-section__sub">{{ $section['description'] }}</div>
                    @endif
                </header>

                @php
                    $allSingletons = $palettes->every(fn ($key) => ! is_array($colors[$key]));
                @endphp

                @if ($allSingletons)
                    {{-- Mixed/single-value palettes render in one grid --}}
                    <div class="ds-swatch-grid">
                        @foreach ($palettes as $key)
                            <x-filament-design-system::swatch :label="$key" :hex="$colors[$key]" />
                        @endforeach
                    </div>
                @else
                    @foreach ($palettes as $key)
                        @php $palette = $colors[$key]; @endphp
                        @if ($palettes->count() > 1)
                            <div class="ds-section__group-label">{{ $key }}</div>
                        @endif
                        @if (is_array($palette))
                            <div class="ds-swatch-grid">
                                @foreach ($palette as $shade => $hex)
                                    <x-filament-design-system::swatch :label="$key . '-' . $shade" :hex="$hex" />
                                @endforeach
                            </div>
                        @else
                            <div class="ds-swatch-grid">
                                <x-filament-design-system::swatch :label="$key" :hex="$palette" />
                            </div>
                        @endif
                    @endforeach
                @endif
            </section>
        @endforeach

        {{-- Spacing & Radius --}}
        <section class="ds-section">
            <header class="ds-section__head">
                <div class="ds-section__title">Spacing & Radius</div>
            </header>
            <div class="ds-two-col">
                <div>
                    <div class="ds-section__group-label">Spacing scale</div>
                    @foreach ($spacing as $step => $rem)
                        <div class="ds-space-row">
                            <code class="ds-space-row__tag">{{ $step }}</code>
                            <div class="ds-space-row__bar" style="width: {{ $rem }};"></div>
                            <code class="ds-space-row__num">{{ $rem }}</code>
                        </div>
                    @endforeach
                </div>
                <div>
                    <div class="ds-section__group-label">Radius scale</div>
                    <div class="ds-radius-grid">
                        @foreach ($radius as $name => $rem)
                            <div class="ds-radius-cell">
                                <div class="ds-radius-cell__shape" style="border-radius: {{ $rem }};"></div>
                                <code class="ds-radius-cell__name">{{ $name }}</code>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- All tokens --}}
        <section class="ds-section">
            <header class="ds-section__head">
                <div class="ds-section__title">All Tokens</div>
                <div class="ds-section__sub">Flat CSS-variable view. This is the surface a token editor (or LLM) writes against.</div>
            </header>
            <div style="overflow-x: auto;">
                <table class="ds-tokens">
                    <thead>
                        <tr>
                            <th class="ds-tokens__col-swatch"></th>
                            <th>Variable</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($flat as $name => $value)
                            <tr>
                                <td class="ds-tokens__col-swatch">
                                    @if (\Visualbuilder\FilamentDesignSystem\Theme\Tokens::looksLikeColor($value))
                                        <span class="ds-tokens__swatch" style="background-color: {{ $value }};"></span>
                                    @endif
                                </td>
                                <td>{{ $name }}</td>
                                <td>{{ $value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
