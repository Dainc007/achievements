<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Concerns;

use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Models\AchievementProgress;
use Dainc007\Achievements\Support\Badge;
use Dainc007\Achievements\Support\BadgeCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Convenience for a subject model (User, Team, …) that earns achievements.
 * Provides the polymorphic relations and a default {@see Awardable} key. The
 * model must still implement the Awardable interface.
 *
 * @phpstan-require-extends Model
 */
trait HasAchievements
{
    public function awardableKey(): string
    {
        return $this->getMorphClass().':'.$this->getKey();
    }

    /**
     * @return MorphMany<AchievementAward, $this>
     */
    public function achievementAwards(): MorphMany
    {
        return $this->morphMany(AchievementAward::class, 'subject');
    }

    /**
     * @return MorphMany<AchievementProgress, $this>
     */
    public function achievementProgress(): MorphMany
    {
        return $this->morphMany(AchievementProgress::class, 'subject');
    }

    /**
     * Every active achievement as a badge view-model for this subject.
     *
     * @return Collection<int, Badge>
     */
    public function badges(): Collection
    {
        return BadgeCollection::for($this);
    }
}
