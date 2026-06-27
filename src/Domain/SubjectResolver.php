<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Domain;

use Dainc007\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Supplies the set of subjects to evaluate for an achievement during a
 * backfill. The engine doesn't know the consuming app's models, so the app
 * implements this — returning a *query* (not a collection) so the recalculate
 * command can narrow it (exclude already-awarded subjects) and chunk it.
 *
 * The query's model must implement {@see Awardable}.
 */
interface SubjectResolver
{
    /**
     * @return Builder<Model>
     */
    public function query(Achievement $achievement): Builder;
}
