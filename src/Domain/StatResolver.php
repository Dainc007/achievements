<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Domain;

/**
 * Resolves the current value of a named stat for a subject.
 *
 * The engine ships an interface only; the consuming app supplies an
 * implementation (e.g. backed by OnSide's Statistic model). Keeping this
 * abstract lets the config-driven evaluators stay framework-free and testable.
 */
interface StatResolver
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function value(Awardable $subject, string $statKey, array $context): int;
}
