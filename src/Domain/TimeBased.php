<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Domain;

/**
 * Marks an evaluator that advances on a daily schedule rather than in reaction
 * to a domain event. The `achievements:tick` command processes only achievements
 * whose evaluator implements this.
 */
interface TimeBased {}
