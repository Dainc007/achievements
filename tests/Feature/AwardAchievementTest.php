<?php

declare(strict_types=1);

use Dainc007\Achievements\Actions\AwardAchievement;
use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Enums\AwardOutcome;
use Dainc007\Achievements\Events\AchievementUnlocked;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Models\AchievementProgress;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Dainc007\Achievements\Tests\Fixtures\TestSubject;
use Illuminate\Support\Facades\Event;

/**
 * Registers an evaluator whose progress is dictated by the context, so tests
 * can drive completion deterministically.
 */
function registerControllableEvaluator(string $type = 'test'): void
{
    app(EvaluatorRegistry::class)->register($type, new class implements Evaluator
    {
        public function progress(Awardable $subject, array $context): Progress
        {
            return new Progress(
                current: (int) ($context['current'] ?? 0),
                target: (int) ($context['target'] ?? 0),
            );
        }
    });
}

function makeAchievement(string $type = 'test'): Achievement
{
    return Achievement::create(['key' => 'k_'.$type, 'name' => 'K', 'type' => $type]);
}

beforeEach(function (): void {
    registerControllableEvaluator();
});

it('awards an achievement and fires the event when progress is complete', function (): void {
    Event::fake([AchievementUnlocked::class]);
    $subject = TestSubject::create(['name' => 'Ada']);
    $achievement = makeAchievement();

    $outcome = app(AwardAchievement::class)->handle($subject, $achievement, ['current' => 10, 'target' => 10]);

    expect($outcome)->toBe(AwardOutcome::Awarded)
        ->and(AchievementAward::query()->where('achievement_id', $achievement->id)->count())->toBe(1);

    Event::assertDispatched(AchievementUnlocked::class, 1);
});

it('records partial progress without awarding when incomplete', function (): void {
    Event::fake([AchievementUnlocked::class]);
    $subject = TestSubject::create(['name' => 'Bo']);
    $achievement = makeAchievement();

    $outcome = app(AwardAchievement::class)->handle($subject, $achievement, ['current' => 4, 'target' => 10]);

    expect($outcome)->toBe(AwardOutcome::Progressed)
        ->and(AchievementAward::query()->count())->toBe(0);

    $progress = AchievementProgress::query()->firstOrFail();
    expect($progress->current)->toBe(4)->and($progress->target)->toBe(10);

    Event::assertNotDispatched(AchievementUnlocked::class);
});

it('is idempotent: firing the same completion twice awards once and fires once', function (): void {
    Event::fake([AchievementUnlocked::class]);
    $subject = TestSubject::create(['name' => 'Cy']);
    $achievement = makeAchievement();
    $action = app(AwardAchievement::class);

    $first = $action->handle($subject, $achievement, ['current' => 10, 'target' => 10]);
    $second = $action->handle($subject, $achievement, ['current' => 10, 'target' => 10]);

    expect($first)->toBe(AwardOutcome::Awarded)
        ->and($second)->toBe(AwardOutcome::AlreadyAwarded)
        ->and(AchievementAward::query()->count())->toBe(1);

    Event::assertDispatched(AchievementUnlocked::class, 1);
});

it('advances stored progress across repeated evaluations', function (): void {
    $subject = TestSubject::create(['name' => 'Di']);
    $achievement = makeAchievement();
    $action = app(AwardAchievement::class);

    $action->handle($subject, $achievement, ['current' => 3, 'target' => 10]);
    $action->handle($subject, $achievement, ['current' => 7, 'target' => 10]);

    expect(AchievementProgress::query()->count())->toBe(1)
        ->and(AchievementProgress::query()->firstOrFail()->current)->toBe(7);
});

it('awards independently for different polymorphic subjects', function (): void {
    $achievement = makeAchievement();
    $action = app(AwardAchievement::class);

    $first = TestSubject::create(['name' => 'One']);
    $second = TestSubject::create(['name' => 'Two']);

    $action->handle($first, $achievement, ['current' => 10, 'target' => 10]);
    $action->handle($second, $achievement, ['current' => 10, 'target' => 10]);

    expect(AchievementAward::query()->count())->toBe(2);
});

it('does not re-award when an award already exists', function (): void {
    Event::fake([AchievementUnlocked::class]);
    $subject = TestSubject::create(['name' => 'Ed']);
    $achievement = makeAchievement();

    AchievementAward::create([
        'achievement_id' => $achievement->id,
        'subject_type' => $subject->getMorphClass(),
        'subject_id' => $subject->getKey(),
        'awarded_at' => now(),
    ]);

    $outcome = app(AwardAchievement::class)->handle($subject, $achievement, ['current' => 10, 'target' => 10]);

    expect($outcome)->toBe(AwardOutcome::AlreadyAwarded)
        ->and(AchievementAward::query()->count())->toBe(1);

    Event::assertNotDispatched(AchievementUnlocked::class);
});
