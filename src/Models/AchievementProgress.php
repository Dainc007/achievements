<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Partial progress a subject has toward a (typically progressive) achievement.
 * One row per (achievement, subject); updated as qualifying events arrive.
 */
final class AchievementProgress extends Model
{
    protected $table = 'achievement_progress';

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
            'current' => 'integer',
            'target' => 'integer',
        ];
    }
}
