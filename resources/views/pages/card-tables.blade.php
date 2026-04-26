<x-filament-panels::page>
    <p style="opacity: 0.7; margin-bottom: 1rem;">
        The same nine records rendered as a Filament table with <code>contentGrid([...])</code> applied.
        Toggle the density below to compare full-width, two-column, and three-column layouts.
    </p>

    {{ $this->form }}

    <div style="margin-top: 1rem;">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
