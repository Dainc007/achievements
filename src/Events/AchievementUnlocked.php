<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Events;

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired exactly once when a subject first completes an achievement. The
 * consuming app listens to this to notify, reward, or broadcast.
 */
final readonly class AchievementUnlocked
{
    use Dispatchable;

    public function __construct(
        public Achievement $achievement,
        public Awardable $subject,
        public AchievementAward $award,
    ) {}
}
