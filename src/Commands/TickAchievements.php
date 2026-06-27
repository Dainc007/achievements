<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Commands;

use Dainc007\Achievements\Actions\AwardAchievement;
use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\ConditionResolver;
use Dainc007\Achievements\Domain\SubjectResolver;
use Dainc007\Achievements\Domain\TimeBased;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Daily trigger for time-based achievements (streak / accumulator). Event-driven
 * evaluation can't see "a day passed with no event", so these are advanced here.
 *
 * Idempotent per day: each progress row records the last day it counted, so
 * running the tick twice in a day never double-counts. Schedule once daily.
 */
final class TickAchievements extends Command
{
    protected $signature = 'achievements:tick {--date= : The day to evaluate (Y-m-d), defaults to today}';

    protected $description = 'Advance time-based (streak/accumulator) achievements for the given day.';

    /**
     * @param  SubjectResolver<Model>  $subjects
     */
    public function handle(
        EvaluatorRegistry $registry,
        SubjectResolver $subjects,
        ConditionResolver $conditions,
        AwardAchievement $award,
    ): int {
        $date = $this->option('date');
        $date = is_string($date) && $date !== ''
            ? Carbon::parse($date)->toDateString()
            : Carbon::now()->toDateString();

        $achievements = Achievement::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (Achievement $achievement): bool => $registry->has($achievement->type)
                && $registry->get($achievement->type) instanceof TimeBased);

        foreach ($achievements as $achievement) {
            $this->tick($achievement, $subjects, $conditions, $award, $date);
        }

        return self::SUCCESS;
    }

    /**
     * @param  SubjectResolver<Model>  $subjects
     */
    private function tick(
        Achievement $achievement,
        SubjectResolver $subjects,
        ConditionResolver $conditions,
        AwardAchievement $award,
        string $date,
    ): void {
        $subjects->query($achievement)->lazyById()->each(
            function (Model $subject) use ($achievement, $conditions, $award, $date): void {
                if (! $subject instanceof Awardable) {
                    return;
                }

                $award->handle($subject, $achievement, [
                    'date' => $date,
                    'qualifies' => $conditions->qualifies($subject, $achievement),
                ]);
            }
        );

        $this->info("Ticked [{$achievement->key}] for {$date}.");
    }
}
