<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A completed (unlocked) achievement for a polymorphic subject. The
 * (achievement_id, subject_type, subject_id) uniqueness guarantees idempotency.
 *
 * @property int $id
 * @property int $achievement_id
 * @property string $subject_type
 * @property int $subject_id
 * @property Carbon $awarded_at
 */
final class AchievementAward extends Model
{
    protected $guarded = [];

    /**
     * @return BelongsTo<Achievement, $this>
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
        ];
    }
}
