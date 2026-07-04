<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Evaluators;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Domain\StatResolver;
use InvalidArgumentException;

/**
 * A percentage of one stat over another, gated by a minimum sample size:
 * "win 70% of games with at least 100 played". Below the minimum the progress
 * is 0 (not yet eligible), so a lucky 1-from-1 never qualifies.
 *
 * Expected config shape:
 *   ['stat' => string, 'per' => string, 'target' => int, 'min' => int]
 *   e.g. ['stat' => 'games_won', 'per' => 'games_played', 'target' => 70, 'min' => 100]
 */
final readonly class RatioEvaluator implements Evaluator
{
    public function __construct(private StatResolver $stats) {}

    public function progress(Awardable $subject, array $context): Progress
    {
        /** @var array<string, mixed> $config */
        $config = $context['config'] ?? [];

        $statKey = $config['stat'] ?? null;
        $perKey = $config['per'] ?? null;

        if (! is_string($statKey) || $statKey === '' || ! is_string($perKey) || $perKey === '') {
            throw new InvalidArgumentException(
                'RatioEvaluator requires non-empty "stat" and "per" keys in config.',
            );
        }

        $target = (int) ($config['target'] ?? 0);
        $min = (int) ($config['min'] ?? 0);

        $denominator = $this->stats->value($subject, $perKey, $context);

        // Not enough of a sample yet — not eligible, report no progress.
        if ($denominator <= 0 || $denominator < $min) {
            return new Progress(current: 0, target: $target);
        }

        $numerator = $this->stats->value($subject, $statKey, $context);
        $percent = (int) floor($numerator / $denominator * 100);

        return new Progress(current: $percent, target: $target);
    }
}
