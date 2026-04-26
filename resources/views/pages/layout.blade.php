<x-filament-panels::page>
    <form wire:submit="demoSubmit">
        {{ $this->form }}

        <x-filament::actions
            class="mt-6"
            :actions="$this->getFormActions()"
        />
    </form>
</x-filament-panels::page>
