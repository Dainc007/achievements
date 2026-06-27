<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Evaluators;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Domain\TimeBased;
use Illuminate\Support\Carbon;

/**
 * Counts *consecutive* qualifying days ("be #1 for 10 days in a row"). The
 * streak resets to 0 on a non-qualifying day and to 1 after a gap. Driven by the
 * daily tick, which supplies `qualifies` (today's state) and `date`.
 *
 * Expected context: `qualifies` (bool), `date` (Y-m-d), `config.target` (int),
 * and the prior `progress` (`current` + `meta.last_counted_on`).
 */
final readonly class StreakEvaluator implements Evaluator, TimeBased
{
    public function progress(Awardable $subject, array $context): Progress
    {
        $target = (int) ($context['config']['target'] ?? 0);
        $date = (string) ($context['date'] ?? '');
        $qualifies = (bool) ($context['qualifies'] ?? false);

        $prior = $context['progress'] ?? [];
        $priorCurrent = (int) ($prior['current'] ?? 0);
        $lastCountedOn = $prior['meta']['last_counted_on'] ?? null;

        if (! $qualifies) {
            return new Progress(0, $target, ['last_counted_on' => null]);
        }

        // Already counted today — keep the streak as-is (idempotent re-run).
        if ($lastCountedOn === $date) {
            return new Progress($priorCurrent, $target, ['last_counted_on' => $date]);
        }

        $yesterday = Carbon::parse($date)->subDay()->toDateString();
        $current = $lastCountedOn === $yesterday ? $priorCurrent + 1 : 1;

        return new Progress($current, $target, ['last_counted_on' => $date]);
    }
}
