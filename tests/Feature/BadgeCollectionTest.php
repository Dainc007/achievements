<?php

declare(strict_types=1);

use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Models\AchievementProgress;
use Dainc007\Achievements\Support\Badge;
use Dainc007\Achievements\Support\BadgeCollection;
use Dainc007\Achievements\Tests\Fixtures\TestSubject;

function achievementNamed(string $key, bool $active = true): Achievement
{
    return Achievement::create([
        'key' => $key,
        'name' => ucfirst($key),
        'type' => 'stat_threshold',
        'config' => ['target' => 10],
        'is_active' => $active,
    ]);
}

it('returns a badge for every active achievement', function (): void {
    $subject = TestSubject::create(['name' => 'Ada']);
    achievementNamed('one');
    achievementNamed('two');

    $badges = BadgeCollection::for($subject);

    expect($badges)->toHaveCount(2)
        ->and($badges->first())->toBeInstanceOf(Badge::class);
});

it('excludes inactive achievements', function (): void {
    $subject = TestSubject::create(['name' => 'Ada']);
    achievementNamed('active');
    achievementNamed('hidden', active: false);

    expect(BadgeCollection::for($subject)->pluck('achievement.key')->all())
        ->toBe(['active']);
});

it('marks earned achievements with their awarded date', function (): void {
    $subject = TestSubject::create(['name' => 'Ada']);
    $achievement = achievementNamed('earned');

    AchievementAward::create([
        'achievement_id' => $achievement->id,
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->id,
        'awarded_at' => now(),
    ]);

    $badge = BadgeCollection::for($subject)->first();

    expect($badge->earned)->toBeTrue()
        ->and($badge->percent())->toBe(100.0)
        ->and($badge->awardedAt)->not->toBeNull();
});

it('reports in-progress counters for unearned achievements', function (): void {
    $subject = TestSubject::create(['name' => 'Ada']);
    $achievement = achievementNamed('progressing');

    AchievementProgress::create([
        'achievement_id' => $achievement->id,
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->id,
        'current' => 4,
        'target' => 10,
    ]);

    $badge = BadgeCollection::for($subject)->first();

    expect($badge->earned)->toBeFalse()
        ->and($badge->isInProgress())->toBeTrue()
        ->and($badge->current)->toBe(4)
        ->and($badge->target)->toBe(10)
        ->and($badge->percent())->toBe(40.0);
});

it('treats a revoked award as not earned', function (): void {
    $subject = TestSubject::create(['name' => 'Ada']);
    $achievement = achievementNamed('lapsed');

    AchievementAward::create([
        'achievement_id' => $achievement->id,
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->id,
        'awarded_at' => now()->subDay(),
        'revoked_at' => now(),
    ]);

    expect(BadgeCollection::for($subject)->first()->earned)->toBeFalse();
});

it('shows an empty subject every achievement as unearned', function (): void {
    $subject = TestSubject::create(['name' => 'Newbie']);
    achievementNamed('one');
    achievementNamed('two');

    $badges = BadgeCollection::for($subject);

    expect($badges)->toHaveCount(2)
        ->and($badges->every(fn (Badge $b): bool => ! $b->earned))->toBeTrue();
});
