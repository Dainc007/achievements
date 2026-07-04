@php($achievement = $badge->achievement)
@php($imageUrl = $achievement->image
    ? \Illuminate\Support\Facades\Storage::disk(config('achievements.image_disk', 'public'))->url($achievement->image)
    : null)

<div @class([
    'rounded-xl border p-4 shadow-sm transition',
    'border-amber-300 bg-amber-50 dark:border-amber-400/30 dark:bg-amber-400/10' => $badge->earned,
    'border-gray-200 bg-white dark:border-white/10 dark:bg-white/5' => ! $badge->earned,
])>
    <div class="flex items-center gap-3">
        @if ($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $achievement->name }}"
                @class([
                    'h-8 w-8 shrink-0 rounded object-cover',
                    'opacity-40 grayscale' => ! $badge->earned,
                ])
            />
        @else
            <x-filament::icon
                :icon="\Dainc007\Achievements\Filament\Support\BadgeIcon::resolve($achievement->icon)"
                @class([
                    'h-8 w-8 shrink-0',
                    'text-amber-500' => $badge->earned,
                    'text-gray-400 dark:text-gray-500' => ! $badge->earned,
                ])
            />
        @endif

        <div class="min-w-0">
            <p class="truncate font-semibold text-gray-950 dark:text-white">{{ $achievement->name }}</p>

            @if ($achievement->tier)
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $achievement->tier->value }}</p>
            @endif
        </div>
    </div>

    @if ($achievement->description)
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $achievement->description }}</p>
    @endif

    @if ($badge->earned)
        <p class="mt-3 text-xs font-medium text-amber-700 dark:text-amber-300">
            {{ __('achievements::achievements.earned') }}@if ($badge->awardedAt) · {{ $badge->awardedAt->isoFormat('LL') }}@endif
        </p>
    @elseif ($badge->isInProgress())
        <div class="mt-3">
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                <div class="h-full rounded-full bg-primary-500" style="width: {{ $badge->percent() }}%"></div>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $badge->current }} / {{ $badge->target }}</p>
        </div>
    @else
        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">{{ __('achievements::achievements.locked') }}</p>
    @endif
</div>
