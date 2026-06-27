<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Models;

use Dainc007\Achievements\Enums\Tier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An achievement definition: what can be earned, how it is evaluated, and how
 * it is presented. Authored in the admin UI or seeded by the consuming app.
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property array<string, mixed>|null $config
 * @property string|null $icon
 * @property Tier|null $tier
 * @property string|null $category
 * @property bool $is_progressive
 * @property int|null $points
 * @property bool $is_active
 */
final class Achievement extends Model
{
    protected $guarded = [];

    /**
     * @return HasMany<AchievementAward, $this>
     */
    public function awards(): HasMany
    {
        return $this->hasMany(AchievementAward::class);
    }

    /**
     * @return HasMany<AchievementProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(AchievementProgress::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'tier' => Tier::class,
            'is_progressive' => 'boolean',
            'is_active' => 'boolean',
            'points' => 'integer',
        ];
    }
}
