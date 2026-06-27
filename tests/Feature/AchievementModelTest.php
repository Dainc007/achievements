<?php

declare(strict_types=1);

use Dainc007\Achievements\Enums\Tier;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Models\AchievementProgress;
use Illuminate\Database\QueryException;

it('persists an achievement with casted attributes', function (): void {
    $achievement = Achievement::create([
        'key' => 'goal_machine',
        'name' => 'Goal Machine',
        'description' => 'Score 50 goals.',
        'type' => 'stat_threshold',
        'config' => ['stat' => 'goals', 'target' => 50],
        'icon' => 'heroicon-o-trophy',
        'tier' => Tier::Gold,
        'category' => 'scoring',
        'is_progressive' => true,
        'points' => null,
        'is_active' => true,
    ]);

    $fresh = $achievement->fresh();

    expect($fresh->config)->toBe(['stat' => 'goals', 'target' => 50])
        ->and($fresh->tier)->toBe(Tier::Gold)
        ->and($fresh->is_progressive)->toBeTrue()
        ->and($fresh->is_active)->toBeTrue()
        ->and($fresh->points)->toBeNull();
});

it('enforces a unique achievement key', function (): void {
    Achievement::create(['key' => 'dupe', 'name' => 'One', 'type' => 'stat_threshold']);
    Achievement::create(['key' => 'dupe', 'name' => 'Two', 'type' => 'stat_threshold']);
})->throws(QueryException::class);

it('has many awards and progress records', function (): void {
    $achievement = Achievement::create(['key' => 'k', 'name' => 'K', 'type' => 'stat_threshold']);

    $achievement->awards()->create([
        'subject_type' => 'user',
        'subject_id' => 1,
        'awarded_at' => now(),
    ]);
    $achievement->progress()->create([
        'subject_type' => 'user',
        'subject_id' => 2,
        'current' => 3,
        'target' => 10,
    ]);

    expect($achievement->awards)->toHaveCount(1)
        ->and($achievement->awards->first())->toBeInstanceOf(AchievementAward::class)
        ->and($achievement->progress)->toHaveCount(1)
        ->and($achievement->progress->first())->toBeInstanceOf(AchievementProgress::class);
});

it('relates an award back to its achievement and stores the polymorphic subject', function (): void {
    $achievement = Achievement::create(['key' => 'k2', 'name' => 'K2', 'type' => 'stat_threshold']);

    $award = AchievementAward::create([
        'achievement_id' => $achievement->id,
        'subject_type' => 'team',
        'subject_id' => 7,
        'awarded_at' => now(),
    ]);

    expect($award->achievement->is($achievement))->toBeTrue()
        ->and($award->subject_type)->toBe('team')
        ->and($award->subject_id)->toBe(7);
});

it('casts progress counters to integers', function (): void {
    $achievement = Achievement::create(['key' => 'k3', 'name' => 'K3', 'type' => 'stat_threshold']);

    $progress = AchievementProgress::create([
        'achievement_id' => $achievement->id,
        'subject_type' => 'user',
        'subject_id' => 1,
        'current' => '4',
        'target' => '20',
    ]);

    expect($progress->fresh()->current)->toBe(4)
        ->and($progress->fresh()->target)->toBe(20);
});
