<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Domain;

/**
 * The progress a subject has toward a single achievement.
 *
 * Pure-PHP value object — no framework dependencies — so the awarding logic
 * can be reasoned about and unit-tested in isolation.
 */
final readonly class Progress
{
    /**
     * @param  array<string, mixed>  $meta  Evaluator bookkeeping persisted on the
     *                                      progress row (e.g. last-counted day for ticks).
     */
    public function __construct(
        public int $current,
        public int $target,
        public array $meta = [],
    ) {}

    public function isComplete(): bool
    {
        return $this->current >= $this->target;
    }

    public function percent(): float
    {
        if ($this->target <= 0) {
            return 0.0;
        }

        return min(100.0, $this->current / $this->target * 100);
    }
}
