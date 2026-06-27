<x-filament-panels::page>
    @php($registry = app(\Dainc007\Achievements\Filament\Support\BadgeRendererRegistry::class))
    @php($badges = $this->getBadges())

    @if ($badges->isEmpty())
        <x-filament::section>
            {{ __('achievements::achievements.empty') }}
        </x-filament::section>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($badges as $badge)
                @include($registry->viewFor($badge->achievement->category), ['badge' => $badge])
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
