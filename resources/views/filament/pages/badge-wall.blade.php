<x-filament-panels::page>
    @php
        $registry = app(\Dainc007\Achievements\Filament\Support\BadgeRendererRegistry::class);
        $badges = $this->getBadges();

        $total = $badges->count();
        $earnedCount = $badges->where('earned', true)->count();
        $inProgressCount = $badges->filter->isInProgress()->count();
        $points = $badges->where('earned', true)->sum(fn ($b) => (int) ($b->achievement->points ?? 0));
        $percent = $total > 0 ? (int) round($earnedCount / $total * 100) : 0;
    @endphp

    @if ($badges->isEmpty())
        <x-filament::section>
            {{ __('achievements::achievements.empty') }}
        </x-filament::section>
    @else
        {{-- Summary band: overall progress + headline counts --}}
        <div class="rounded-xl bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950 p-5 ring-1 ring-white/10 sm:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    {{-- Completion ring --}}
                    <div
                        class="relative flex size-16 shrink-0 items-center justify-center rounded-full"
                        style="background: conic-gradient(rgb(var(--primary-500)) {{ $percent }}%, rgba(255,255,255,0.08) {{ $percent }}%);"
                    >
                        <div class="flex size-12 items-center justify-center rounded-full bg-gray-900">
                            <span class="text-sm font-bold text-white">{{ $percent }}%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-white">{{ __('achievements::achievements.title') }}</p>
                        <p class="text-sm text-gray-400">
                            {{ __('achievements::achievements.summary.completion', ['percent' => $percent]) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 sm:gap-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-primary-400">{{ $earnedCount }}<span class="text-base font-medium text-gray-500">/{{ $total }}</span></p>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('achievements::achievements.summary.earned') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white">{{ $inProgressCount }}</p>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('achievements::achievements.summary.in_progress') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-amber-300">{{ number_format($points) }}</p>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('achievements::achievements.summary.points') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($badges as $badge)
                @include($registry->viewFor($badge->achievement->category), ['badge' => $badge])
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
