<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Events;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when a revocable award lapses (its condition is no longer met). The
 * award row is kept (soft-revoked via revoked_at) so history survives.
 */
final readonly class AchievementRevoked
{
    use Dispatchable;

    public function __construct(
        public Achievement $achievement,
        public Awardable $subject,
        public AchievementAward $award,
    ) {}
}
