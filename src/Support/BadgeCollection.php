<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Support;

use Dainc007\Achievements\Concerns\HasAchievements;
use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Models\AchievementProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Builds the set of {@see Badge} view-models for a subject — every active
 * achievement, marked earned or with its in-progress counters. This is the data
 * source for the badge wall, kept separate from any UI so it is directly testable.
 */
final class BadgeCollection
{
    /**
     * Every active achievement as a badge, eager (no pagination). Used by the
     * {@see HasAchievements::badges()} helper.
     *
     * @return Collection<int, Badge>
     */
    public static function for(Model&Awardable $subject): Collection
    {
        /** @var Collection<int, Achievement> $achievements */
        $achievements = Achievement::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return self::hydrate($subject, $achievements)->values();
    }

    /**
     * A paginated, searchable, sortable page of badges — so a wall with hundreds
     * of achievements renders only one page of cards, not all of them at once.
     *
     * @return LengthAwarePaginator<int, Badge>
     */
    public static function paginateFor(
        Model&Awardable $subject,
        int $perPage = 12,
        string $search = '',
        string $sort = 'status',
    ): LengthAwarePaginator {
        $query = Achievement::query()->where('is_active', true);

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('key', 'like', '%'.$search.'%');
            });
        }

        self::applySort($query, $sort, $subject);

        $page = $query->paginate($perPage);

        /** @var Collection<int, Achievement> $achievements */
        $achievements = collect($page->items());
        $badges = self::hydrate($subject, $achievements);

        // Rebuild as a Badge paginator, preserving the page metadata, rather than
        // mutating the Achievement-typed one.
        return new LengthAwarePaginator(
            $badges,
            $page->total(),
            $page->perPage(),
            $page->currentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()],
        );
    }

    /**
     * Turn a set of achievements into badge view-models for the subject, loading
     * the subject's awards + progress for only those achievements.
     *
     * @param  Collection<int, Achievement>  $achievements
     * @return Collection<int, Badge>
     */
    private static function hydrate(Model&Awardable $subject, Collection $achievements): Collection
    {
        $subjectType = $subject->getMorphClass();
        $subjectId = $subject->getKey();
        $ids = $achievements->map->getKey()->all();

        $awards = AchievementAward::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->whereIn('achievement_id', $ids)
            ->whereNull('revoked_at')
            ->get()
            ->keyBy('achievement_id');

        $progress = AchievementProgress::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->whereIn('achievement_id', $ids)
            ->get()
            ->keyBy('achievement_id');

        return $achievements->map(function (Achievement $achievement) use ($awards, $progress): Badge {
            $award = $awards->get($achievement->getKey());
            $row = $progress->get($achievement->getKey());

            $target = $row->target ?? (int) ($achievement->config['target'] ?? 0);

            return new Badge(
                achievement: $achievement,
                earned: $award !== null,
                current: $award !== null ? max($target, $row->current ?? 0) : ($row->current ?? 0),
                target: $target,
                awardedAt: $award?->awarded_at,
            );
        });
    }

    /**
     * @param  Builder<Achievement>  $query
     */
    private static function applySort(Builder $query, string $sort, Model&Awardable $subject): void
    {
        match ($sort) {
            'name' => $query->orderBy('name'),
            'newest' => $query->orderByDesc('id'),
            'tier' => $query->orderByRaw(
                "case tier when 'bronze' then 1 when 'silver' then 2 when 'gold' then 3 when 'legendary' then 4 else 5 end"
            ),
            // Default "status": earned badges first, then by id for stability.
            default => $query
                ->orderByRaw(
                    'exists (select 1 from achievement_awards aa where aa.achievement_id = achievements.id'
                    .' and aa.subject_type = ? and aa.subject_id = ? and aa.revoked_at is null) desc',
                    [$subject->getMorphClass(), $subject->getKey()],
                )
                ->orderBy('id'),
        };
    }
}
