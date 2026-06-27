<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Actions;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Enums\AwardOutcome;
use Dainc007\Achievements\Enums\Retention;
use Dainc007\Achievements\Events\AchievementRevoked;
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

        $activeAward = AchievementAward::query()
            ->where('achievement_id', $achievement->getKey())
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->whereNull('revoked_at')
            ->first();

        $isRevocable = $achievement->retention === Retention::Revocable;

        // Permanent achievements never need re-evaluating once held.
        if ($activeAward !== null && ! $isRevocable) {
            return AwardOutcome::AlreadyAwarded;
        }

        $progressRow = AchievementProgress::query()
            ->where('achievement_id', $achievement->getKey())
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->first();

        $progress = $this->registry->get($achievement->type)->progress($subject, [
            ...$context,
            'config' => $achievement->config ?? [],
            // Stateful evaluators (streak/accumulator) read the prior state.
            'progress' => [
                'current' => $progressRow->current ?? 0,
                'meta' => $progressRow->meta ?? [],
            ],
        ]);

        AchievementProgress::query()->updateOrCreate(
            [
                'achievement_id' => $achievement->getKey(),
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
            ],
            [
                'current' => $progress->current,
                'target' => $progress->target,
                'meta' => $progress->meta,
            ],
        );

        if ($progress->isComplete()) {
            if ($activeAward !== null) {
                return AwardOutcome::AlreadyAwarded;
            }

            return $this->award($achievement, $subject, $subjectType, $subjectId, $context);
        }

        // Not complete: a held revocable award has lapsed and must be revoked.
        if ($isRevocable && $activeAward !== null) {
            $activeAward->update(['revoked_at' => now()]);

            AchievementRevoked::dispatch($achievement, $subject, $activeAward);

            return AwardOutcome::Revoked;
        }

        return AwardOutcome::Progressed;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function award(
        Achievement $achievement,
        Model&Awardable $subject,
        string $subjectType,
        int|string $subjectId,
        array $context,
    ): AwardOutcome {
        $award = AchievementAward::create([
            'achievement_id' => $achievement->getKey(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'awarded_at' => now(),
            'context' => $context['context'] ?? null,
        ]);

        AchievementUnlocked::dispatch($achievement, $subject, $award);

        return AwardOutcome::Awarded;
    }
}
