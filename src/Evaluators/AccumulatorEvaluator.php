<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Evaluators;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Domain\TimeBased;

/**
 * Adds +1 for each qualifying day ("each day at #1 = +1 point"), never resetting.
 * A non-qualifying day simply doesn't count. Driven by the daily tick.
 *
 * Expected context: `qualifies` (bool), `date` (Y-m-d), `config.target` (int),
 * and the prior `progress` (`current` + `meta.last_counted_on`).
 */
final readonly class AccumulatorEvaluator implements Evaluator, TimeBased
{
    public function progress(Awardable $subject, array $context): Progress
    {
        $target = (int) ($context['config']['target'] ?? 0);
        $date = (string) ($context['date'] ?? '');
        $qualifies = (bool) ($context['qualifies'] ?? false);

        $prior = $context['progress'] ?? [];
        $priorCurrent = (int) ($prior['current'] ?? 0);
        $lastCountedOn = $prior['meta']['last_counted_on'] ?? null;

        // Non-qualifying day, or already counted today: no change (idempotent).
        if (! $qualifies || $lastCountedOn === $date) {
            return new Progress($priorCurrent, $target, ['last_counted_on' => $lastCountedOn]);
        }

        return new Progress($priorCurrent + 1, $target, ['last_counted_on' => $date]);
    }
}
