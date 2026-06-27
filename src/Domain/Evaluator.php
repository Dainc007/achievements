<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Domain;

/**
 * One strategy per achievement TYPE. This is where "different requirements
 * reading different data" lives — each evaluator queries whatever it needs
 * through injected dependencies and reports how far a subject has progressed.
 */
interface Evaluator
{
    /**
     * @param  array<string, mixed>  $context  Event payload + the achievement's config.
     */
    public function progress(Awardable $subject, array $context): Progress;
}
