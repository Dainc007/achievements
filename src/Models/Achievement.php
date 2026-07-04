<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Models;

use Dainc007\Achievements\Enums\Retention;
use Dainc007\Achievements\Enums\Tier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

/**
 * An achievement definition: what can be earned, how it is evaluated, and how
 * it is presented. Authored in the admin UI or seeded by the consuming app.
 *
 * `name` and `description` are stored as per-locale JSON maps
 * ({"en": "...", "pl": "..."}). Read them for display via {@see self::$displayName}
 * / {@see self::$displayDescription}, which resolve the active locale.
 *
 * @property int $id
 * @property string $key
 * @property array<string, string> $name
 * @property array<string, string>|null $description
 * @property string $type
 * @property array<string, mixed>|null $config
 * @property string|null $icon
 * @property string|null $image
 * @property Tier|null $tier
 * @property string|null $category
 * @property bool $is_progressive
 * @property Retention $retention
 * @property int|null $points
 * @property bool $is_active
 * @property-read string $displayName
 * @property-read string|null $displayDescription
 */
final class Achievement extends Model
{
    protected $guarded = [];

    /**
     * Attributes stored as a per-locale JSON map.
     *
     * @var list<string>
     */
    public const TRANSLATABLE = ['name', 'description'];

    /**
     * The active-locale name for display (falls back to app.fallback_locale,
     * then any set locale, then the key).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->localize($this->name) ?? $this->key;
    }

    /**
     * The active-locale description for display, or null when unset.
     */
    public function getDisplayDescriptionAttribute(): ?string
    {
        return $this->localize($this->description);
    }

    /**
     * Resolve a per-locale map to the active locale's value. Tolerates a plain
     * string (legacy / not-yet-migrated) by returning it as-is.
     *
     * @param  array<string, string>|string|null  $value
     */
    private function localize(array|string|null $value): ?string
    {
        if (is_string($value) || $value === null) {
            return $value;
        }

        $fallback = (string) (config('app.fallback_locale') ?: 'en');

        return $value[app()->getLocale()]
            ?? $value[$fallback]
            ?? Arr::first($value, static fn (mixed $v): bool => filled($v));
    }

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
    #[\Override]
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'config' => 'array',
            'tier' => Tier::class,
            'retention' => Retention::class,
            'is_progressive' => 'boolean',
            'is_active' => 'boolean',
            'points' => 'integer',
        ];
    }
}
