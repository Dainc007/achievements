<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Commands;

use Dainc007\Achievements\Actions\AwardAchievement;
use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\SubjectResolver;
use Dainc007\Achievements\Enums\Retention;
use Dainc007\Achievements\Models\Achievement;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Backfills achievement evaluation for existing subjects — used after a new
 * achievement is added so it can be earned retroactively.
 *
 * On-demand only (never scheduled). Kept cheap on reruns: for permanent
 * achievements, subjects that already hold an active award are excluded, so the
 * per-run cost scales with subjects still missing the award rather than the whole
 * population. Subjects are streamed with lazyById to stay memory-flat.
 */
final class RecalculateAchievements extends Command
{
    protected $signature = 'achievements:recalculate {achievement? : Recalculate only the achievement with this key}';

    protected $description = 'Replay achievement evaluation for existing subjects (retroactive backfill).';

    /**
     * @param  SubjectResolver<Model>  $resolver
     */
    public function handle(SubjectResolver $resolver, AwardAchievement $award): int
    {
        $key = $this->argument('achievement');

        $achievements = Achievement::query()
            ->where('is_active', true)
            ->when(is_string($key), fn (Builder $query): Builder => $query->where('key', $key))
            ->get();

        if ($achievements->isEmpty()) {
            $this->warn('No matching active achievements to recalculate.');

            return self::SUCCESS;
        }

        foreach ($achievements as $achievement) {
            $this->recalculate($achievement, $resolver, $award);
        }

        return self::SUCCESS;
    }

    /**
     * @param  SubjectResolver<Model>  $resolver
     */
    private function recalculate(Achievement $achievement, SubjectResolver $resolver, AwardAchievement $award): void
    {
        $query = $resolver->query($achievement);
        $model = $query->getModel();

        // Permanent awards never change once held — skip subjects that already
        // hold an active award. Revocable ones must be re-checked (to revoke).
        if ($achievement->retention === Retention::Permanent) {
            $this->excludeActivelyAwarded($query, $achievement, $model);
        }

        $count = 0;

        $query->lazyById()->each(function (Model $subject) use ($award, $achievement, &$count): void {
            if ($subject instanceof Awardable) {
                $award->handle($subject, $achievement, ['recalculate' => true]);
                $count++;
            }
        });

        $this->info("Recalculated [{$achievement->key}] for {$count} subject(s).");
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function excludeActivelyAwarded(Builder $query, Achievement $achievement, Model $model): void
    {
        $query->whereNotExists(function (QueryBuilder $sub) use ($achievement, $model): void {
            $sub->select(DB::raw('1'))
                ->from('achievement_awards')
                ->whereColumn('achievement_awards.subject_id', $model->getQualifiedKeyName())
                ->where('achievement_awards.subject_type', $model->getMorphClass())
                ->where('achievement_awards.achievement_id', $achievement->getKey())
                ->whereNull('achievement_awards.revoked_at');
        });
    }
}
