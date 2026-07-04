<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Support;

use Dainc007\Achievements\Domain\Evaluator;

/**
 * Maps an achievement's `type` string to the Evaluator that decides its progress.
 *
 * Config-driven types (e.g. "stat_threshold") share one evaluator instance;
 * bespoke types register their own dedicated implementation.
 */
final class EvaluatorRegistry
{
    /** @var array<string, Evaluator> */
    private array $evaluators = [];

    public function register(string $type, Evaluator $evaluator): self
    {
        $this->evaluators[$type] = $evaluator;

        return $this;
    }

    public function has(string $type): bool
    {
        return isset($this->evaluators[$type]);
    }

    /**
     * Registered evaluator type keys, for building a "type" picker in the UI.
     *
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->evaluators);
    }

    public function get(string $type): Evaluator
    {
        return $this->evaluators[$type] ?? throw UnknownEvaluator::forType($type);
    }
}
