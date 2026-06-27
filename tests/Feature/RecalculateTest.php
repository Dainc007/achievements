<?php

declare(strict_types=1);

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Domain\SubjectResolver;
use Dainc007\Achievements\Enums\Retention;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Dainc007\Achievements\Tests\Fixtures\TestSubject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Registers an evaluator that derives progress from the subject's own `score`
 * column (as a real recalculate would read persisted state), and logs which
 * subjects it evaluated so tests can assert skipping.
 */
function registerScoreEvaluator(string $type = 'score_threshold'): Collection
{
    $log = collect();

    app(EvaluatorRegistry::class)->register($type, new readonly class($log) implements Evaluator
    {
        public function __construct(private Collection $log) {}

        public function progress(Awardable $subject, array $context): Progress
        {
            $this->log->push($subject->awardableKey());

            return new Progress(
                current: (int) $subject->score, // @phpstan-ignore-line (TestSubject)
                target: (int) ($context['config']['target'] ?? 10),
            );
        }
    });

    return $log;
}

function bindSubjectResolver(): void
{
    app()->bind(SubjectResolver::class, fn (): SubjectResolver => new class implements SubjectResolver
    {
        public function query(Achievement $achievement): Builder
        {
            return TestSubject::query();
        }
    });
}

function scoreAchievement(string $key = 'goal_machine', Retention $retention = Retention::Permanent): Achievement
{
    return Achievement::create([
        'key' => $key,
        'name' => 'Goal Machine',
        'type' => 'score_threshold',
        'config' => ['target' => 10],
        'retention' => $retention,
        'is_active' => true,
    ]);
}

beforeEach(function (): void {
    bindSubjectResolver();
});

it('backfills awards for subjects that already qualify', function (): void {
    registerScoreEvaluator();
    $qualifies = TestSubject::create(['name' => 'Ada', 'score' => 15]);
    $tooLow = TestSubject::create(['name' => 'Bo', 'score' => 5]);
    scoreAchievement();

    $this->artisan('achievements:recalculate')->assertSuccessful();

    expect(AchievementAward::query()->count())->toBe(1)
        ->and(AchievementAward::query()->where('subject_id', $qualifies->id)->exists())->toBeTrue()
        ->and(AchievementAward::query()->where('subject_id', $tooLow->id)->exists())->toBeFalse();
});

it('is safe to run repeatedly without duplicating awards', function (): void {
    registerScoreEvaluator();
    TestSubject::create(['name' => 'Ada', 'score' => 15]);
    scoreAchievement();

    $this->artisan('achievements:recalculate')->assertSuccessful();
    $this->artisan('achievements:recalculate')->assertSuccessful();

    expect(AchievementAward::query()->count())->toBe(1);
});

it('skips already-awarded subjects on a permanent achievement', function (): void {
    $log = registerScoreEvaluator();
    $already = TestSubject::create(['name' => 'Held', 'score' => 15]);
    $fresh = TestSubject::create(['name' => 'New', 'score' => 15]);
    $achievement = scoreAchievement();

    AchievementAward::create([
        'achievement_id' => $achievement->id,
        'subject_type' => $already->getMorphClass(),
        'subject_id' => $already->id,
        'awarded_at' => now(),
    ]);

    $this->artisan('achievements:recalculate')->assertSuccessful();

    // The already-awarded subject is never re-evaluated; only the fresh one is.
    expect($log->all())->toBe([$fresh->awardableKey()]);
});

it('processes only the named achievement when a key is given', function (): void {
    registerScoreEvaluator();
    TestSubject::create(['name' => 'Ada', 'score' => 15]);
    $wanted = scoreAchievement('wanted');
    $other = scoreAchievement('other');

    $this->artisan('achievements:recalculate', ['achievement' => 'wanted'])->assertSuccessful();

    expect(AchievementAward::query()->where('achievement_id', $wanted->id)->count())->toBe(1)
        ->and(AchievementAward::query()->where('achievement_id', $other->id)->count())->toBe(0);
});

it('re-evaluates holders of a revocable achievement and revokes the lapsed', function (): void {
    registerScoreEvaluator();
    $subject = TestSubject::create(['name' => 'Slipped', 'score' => 3]); // below target now
    $achievement = scoreAchievement('top_rank', Retention::Revocable);

    AchievementAward::create([
        'achievement_id' => $achievement->id,
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->id,
        'awarded_at' => now(),
    ]);

    $this->artisan('achievements:recalculate')->assertSuccessful();

    expect(AchievementAward::query()->whereNull('revoked_at')->count())->toBe(0)
        ->and(AchievementAward::query()->whereNotNull('revoked_at')->count())->toBe(1);
});

it('ignores inactive achievements', function (): void {
    registerScoreEvaluator();
    TestSubject::create(['name' => 'Ada', 'score' => 15]);
    $achievement = scoreAchievement();
    $achievement->update(['is_active' => false]);

    $this->artisan('achievements:recalculate')->assertSuccessful();

    expect(AchievementAward::query()->count())->toBe(0);
});
