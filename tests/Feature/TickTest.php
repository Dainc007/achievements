<?php

declare(strict_types=1);

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\ConditionResolver;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Domain\SubjectResolver;
use Dainc007\Achievements\Evaluators\AccumulatorEvaluator;
use Dainc007\Achievements\Evaluators\StreakEvaluator;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Models\AchievementProgress;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Dainc007\Achievements\Tests\Fixtures\TestSubject;
use Illuminate\Database\Eloquent\Builder;

/** A subject qualifies today when its `score` column is >= 1. */
function bindTickResolvers(): void
{
    app()->bind(SubjectResolver::class, fn (): SubjectResolver => new class implements SubjectResolver
    {
        public function query(Achievement $achievement): Builder
        {
            return TestSubject::query();
        }
    });

    app()->bind(ConditionResolver::class, fn (): ConditionResolver => new class implements ConditionResolver
    {
        public function qualifies(Awardable $subject, Achievement $achievement): bool
        {
            return (int) $subject->score >= 1; // @phpstan-ignore-line (TestSubject)
        }
    });
}

function streakAchievement(int $target = 3): Achievement
{
    app(EvaluatorRegistry::class)->register('rank_streak', new StreakEvaluator);

    return Achievement::create([
        'key' => 'streak',
        'name' => 'On a run',
        'type' => 'rank_streak',
        'config' => ['target' => $target],
    ]);
}

beforeEach(function (): void {
    bindTickResolvers();
});

it('builds a streak across consecutive days and awards at the target', function (): void {
    $leader = TestSubject::create(['name' => 'L', 'score' => 1]);
    streakAchievement(3);

    $this->artisan('achievements:tick', ['--date' => '2026-06-01'])->assertSuccessful();
    $this->artisan('achievements:tick', ['--date' => '2026-06-02'])->assertSuccessful();

    expect(AchievementAward::query()->count())->toBe(0)
        ->and(AchievementProgress::query()->firstOrFail()->current)->toBe(2);

    $this->artisan('achievements:tick', ['--date' => '2026-06-03'])->assertSuccessful();

    expect(AchievementAward::query()->where('subject_id', $leader->id)->count())->toBe(1)
        ->and(AchievementProgress::query()->firstOrFail()->current)->toBe(3);
});

it('does not double-count when the tick runs twice on the same day', function (): void {
    TestSubject::create(['name' => 'L', 'score' => 1]);
    streakAchievement(10);

    $this->artisan('achievements:tick', ['--date' => '2026-06-01'])->assertSuccessful();
    $this->artisan('achievements:tick', ['--date' => '2026-06-01'])->assertSuccessful();

    expect(AchievementProgress::query()->firstOrFail()->current)->toBe(1);
});

it('resets a streak after a non-qualifying day', function (): void {
    $leader = TestSubject::create(['name' => 'L', 'score' => 1]);
    streakAchievement(10);

    $this->artisan('achievements:tick', ['--date' => '2026-06-01'])->assertSuccessful();

    $leader->update(['score' => 0]); // drops off
    $this->artisan('achievements:tick', ['--date' => '2026-06-02'])->assertSuccessful();

    expect(AchievementProgress::query()->firstOrFail()->current)->toBe(0);

    $leader->update(['score' => 1]); // back on top
    $this->artisan('achievements:tick', ['--date' => '2026-06-03'])->assertSuccessful();

    expect(AchievementProgress::query()->firstOrFail()->current)->toBe(1);
});

it('accumulates qualifying days without resetting across a gap', function (): void {
    app(EvaluatorRegistry::class)->register('days_on_top', new AccumulatorEvaluator);
    $leader = TestSubject::create(['name' => 'L', 'score' => 1]);
    Achievement::create([
        'key' => 'days',
        'name' => 'Mainstay',
        'type' => 'days_on_top',
        'config' => ['target' => 100],
    ]);

    $this->artisan('achievements:tick', ['--date' => '2026-06-01'])->assertSuccessful();

    $leader->update(['score' => 0]); // gap day, doesn't count
    $this->artisan('achievements:tick', ['--date' => '2026-06-02'])->assertSuccessful();

    $leader->update(['score' => 1]);
    $this->artisan('achievements:tick', ['--date' => '2026-06-03'])->assertSuccessful();

    expect(AchievementProgress::query()->firstOrFail()->current)->toBe(2);
});

it('ignores achievements whose evaluator is not time-based', function (): void {
    app(EvaluatorRegistry::class)->register('instant', new class implements Evaluator
    {
        public function progress(Awardable $subject, array $context): Progress
        {
            return new Progress(1, 1);
        }
    });
    TestSubject::create(['name' => 'L', 'score' => 1]);
    Achievement::create(['key' => 'instant', 'name' => 'Instant', 'type' => 'instant']);

    $this->artisan('achievements:tick', ['--date' => '2026-06-01'])->assertSuccessful();

    expect(AchievementProgress::query()->count())->toBe(0)
        ->and(AchievementAward::query()->count())->toBe(0);
});
