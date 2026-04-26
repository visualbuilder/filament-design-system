<x-filament-panels::page>
    <style>
        .ds-icon-preview-svg {
            display: block;
            width: 2.5rem;
            height: 2.5rem;
            margin: 0.25rem auto;
            color: var(--color-primary-500, currentColor);
        }
    </style>

    <x-filament::tabs>
        @foreach ($this->getTabsView() as $tab)
            <x-filament::tabs.item
                :active="$activeTab === $tab['key']"
                :badge="$tab['count']"
                wire:click="setActiveTab({{ \Illuminate\Support\Js::from($tab['key']) }})"
            >
                {{ $tab['label'] }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    {{ $this->table }}
</x-filament-panels::page>
