<?php

declare(strict_types=1);

use Dainc007\Achievements\Concerns\HasAchievements;
use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Illuminate\Database\Eloquent\Model;

/**
 * A subject model opting into the trait.
 */
function memberWithTrait(): Model
{
    return new class extends Model implements Awardable
    {
        use HasAchievements;

        protected $table = 'test_subjects';

        protected $guarded = [];
    };
}

it('derives a default awardable key from morph class and id', function (): void {
    $member = memberWithTrait();
    $member->fill(['name' => 'Ada'])->save();

    expect($member->awardableKey())->toBe($member->getMorphClass().':'.$member->getKey());
});

it('exposes the polymorphic awards relation', function (): void {
    $member = memberWithTrait();
    $member->fill(['name' => 'Ada'])->save();
    $achievement = Achievement::create(['key' => 'k', 'name' => 'K', 'type' => 'stat_threshold']);

    AchievementAward::create([
        'achievement_id' => $achievement->id,
        'subject_type' => $member->getMorphClass(),
        'subject_id' => $member->id,
        'awarded_at' => now(),
    ]);

    expect($member->achievementAwards()->count())->toBe(1);
});

it('builds badges for the subject through the trait', function (): void {
    $member = memberWithTrait();
    $member->fill(['name' => 'Ada'])->save();
    Achievement::create(['key' => 'k', 'name' => 'K', 'type' => 'stat_threshold', 'config' => ['target' => 5]]);

    expect($member->badges())->toHaveCount(1);
});
