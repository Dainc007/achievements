<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Support;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Models\AchievementProgress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Builds the set of {@see Badge} view-models for a subject — every active
 * achievement, marked earned or with its in-progress counters. This is the data
 * source for the badge wall, kept separate from any UI so it is directly testable.
 */
final class BadgeCollection
{
    /**
     * @return Collection<int, Badge>
     */
    public static function for(Model&Awardable $subject): Collection
    {
        $subjectType = $subject->getMorphClass();
        $subjectId = $subject->getKey();

        $awards = AchievementAward::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->whereNull('revoked_at')
            ->get()
            ->keyBy('achievement_id');

        $progress = AchievementProgress::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->get()
            ->keyBy('achievement_id');

        return Achievement::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(function (Achievement $achievement) use ($awards, $progress): Badge {
                $award = $awards->get($achievement->getKey());
                $row = $progress->get($achievement->getKey());

                $target = $row->target ?? (int) ($achievement->config['target'] ?? 0);

                return new Badge(
                    achievement: $achievement,
                    earned: $award !== null,
                    current: $award !== null ? max($target, $row->current ?? 0) : ($row->current ?? 0),
                    target: $target,
                    awardedAt: $award?->awarded_at,
                );
            })
            ->values();
    }
}
