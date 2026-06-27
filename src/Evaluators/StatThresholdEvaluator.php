<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Evaluators;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Domain\StatResolver;
use InvalidArgumentException;

/**
 * The config-driven workhorse: "score 50 goals", "play 100 matches",
 * "10 clean sheets" are all one evaluator. The stat key and target come from
 * the achievement's JSON config, so new achievements of this shape need no code.
 *
 * Expected config shape: `['stat' => string, 'target' => int]`.
 */
final readonly class StatThresholdEvaluator implements Evaluator
{
    public function __construct(private StatResolver $stats) {}

    public function progress(Awardable $subject, array $context): Progress
    {
        /** @var array<string, mixed> $config */
        $config = $context['config'] ?? [];

        $statKey = $config['stat'] ?? null;

        if (! is_string($statKey) || $statKey === '') {
            throw new InvalidArgumentException(
                'StatThresholdEvaluator requires a non-empty "stat" key in config.',
            );
        }

        $target = (int) ($config['target'] ?? 0);
        $current = $this->stats->value($subject, $statKey, $context);

        return new Progress(current: $current, target: $target);
    }
}
