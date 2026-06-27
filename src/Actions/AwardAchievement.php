<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Actions;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Enums\AwardOutcome;
use Dainc007\Achievements\Events\AchievementUnlocked;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Models\AchievementProgress;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Illuminate\Database\Eloquent\Model;

/**
 * Evaluates a single achievement for a single subject, persists progress, and
 * awards (once) when the target is met.
 *
 * Idempotent: the (achievement, subject) unique constraint plus the existing-award
 * short-circuit guarantee the same qualifying event can fire any number of times
 * and produce at most one award and one {@see AchievementUnlocked} event.
 *
 * Deliberately a plain injectable class (no action-package dependency) so the
 * engine stays lean; the consuming app may wrap it in its own Action convention.
 */
final readonly class AwardAchievement
{
    public function __construct(private EvaluatorRegistry $registry) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function handle(Model&Awardable $subject, Achievement $achievement, array $context = []): AwardOutcome
    {
        $subjectType = $subject->getMorphClass();
        $subjectId = $subject->getKey();

        $alreadyAwarded = AchievementAward::query()
            ->where('achievement_id', $achievement->getKey())
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->exists();

        if ($alreadyAwarded) {
            return AwardOutcome::AlreadyAwarded;
        }

        $progress = $this->registry
            ->get($achievement->type)
            ->progress($subject, [...$context, 'config' => $achievement->config ?? []]);

        AchievementProgress::query()->updateOrCreate(
            [
                'achievement_id' => $achievement->getKey(),
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
            ],
            [
                'current' => $progress->current,
                'target' => $progress->target,
            ],
        );

        if (! $progress->isComplete()) {
            return AwardOutcome::Progressed;
        }

        $award = AchievementAward::query()->firstOrCreate(
            [
                'achievement_id' => $achievement->getKey(),
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
            ],
            [
                'awarded_at' => now(),
            ],
        );

        if (! $award->wasRecentlyCreated) {
            return AwardOutcome::AlreadyAwarded;
        }

        AchievementUnlocked::dispatch($achievement, $subject, $award);

        return AwardOutcome::Awarded;
    }
}
