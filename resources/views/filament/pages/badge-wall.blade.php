<x-filament-panels::page>
    @php
        $registry = app(\Dainc007\Achievements\Filament\Support\BadgeRendererRegistry::class);
        $badges = $this->getBadges();
    @endphp

    {{-- Search + sort toolbar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-filament::input.wrapper
            :prefix-icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
            class="w-full sm:max-w-xs"
        >
            <x-filament::input
                type="search"
                wire:model.live.debounce.400ms="search"
                :placeholder="__('achievements::achievements.search_placeholder')"
            />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper class="w-full sm:w-auto">
            <x-filament::input.select wire:model.live="sort">
                @foreach ($this->sortOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    @if ($badges->isEmpty())
        <x-filament::section>
            {{ trim($this->search) !== '' ? __('achievements::achievements.no_results') : __('achievements::achievements.empty') }}
        </x-filament::section>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($badges as $badge)
                @include($registry->viewFor($badge->achievement->category), ['badge' => $badge])
            @endforeach
        </div>

        @if ($badges->hasPages())
            <div>
                {{ $badges->onEachSide(1)->links() }}
            </div>
        @endif
    @endif
</x-filament-panels::page>
