<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Domain;

use Dainc007\Achievements\Models\Achievement;

/**
 * Answers "does this subject qualify *today*?" for a time-based achievement —
 * e.g. is the subject currently #1 in the ranking. Inherently app-specific (it
 * often compares against other subjects), so the consuming app implements it;
 * the daily tick feeds the result into the streak/accumulator evaluators.
 */
interface ConditionResolver
{
    public function qualifies(Awardable $subject, Achievement $achievement): bool;
}
