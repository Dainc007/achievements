<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Support;

use Carbon\CarbonInterface;
use Dainc007\Achievements\Models\Achievement;

/**
 * A view-model describing one achievement's state for a given subject, ready to
 * hand to a badge renderer. Decouples the UI from the Eloquent models.
 */
final readonly class Badge
{
    public function __construct(
        public Achievement $achievement,
        public bool $earned,
        public int $current,
        public int $target,
        public ?CarbonInterface $awardedAt = null,
    ) {}

    public function percent(): float
    {
        if ($this->earned) {
            return 100.0;
        }

        return $this->target > 0 ? min(100.0, $this->current / $this->target * 100) : 0.0;
    }

    public function isInProgress(): bool
    {
        return ! $this->earned && $this->current > 0;
    }
}
