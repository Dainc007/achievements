<?php

declare(strict_types=1);

use Dainc007\Achievements\Actions\AwardAchievement;
use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Enums\AwardOutcome;
use Dainc007\Achievements\Enums\Retention;
use Dainc007\Achievements\Events\AchievementRevoked;
use Dainc007\Achievements\Events\AchievementUnlocked;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Models\AchievementAward;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Dainc007\Achievements\Tests\Fixtures\TestSubject;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    app(EvaluatorRegistry::class)->register('test', new class implements Evaluator
    {
        public function progress(Awardable $subject, array $context): Progress
        {
            return new Progress(
                current: (int) ($context['current'] ?? 0),
                target: (int) ($context['target'] ?? 0),
            );
        }
    });
});

function revocable(): Achievement
{
    return Achievement::create([
        'key' => 'top_rank',
        'name' => 'Top of the table',
        'type' => 'test',
        'retention' => Retention::Revocable,
    ]);
}

function permanent(): Achievement
{
    return Achievement::create([
        'key' => 'milestone',
        'name' => 'Milestone',
        'type' => 'test',
        'retention' => Retention::Permanent,
    ]);
}

it('defaults a new achievement to permanent retention', function (): void {
    expect(permanent()->fresh()->retention)->toBe(Retention::Permanent);
});

it('awards a revocable achievement while its condition holds', function (): void {
    Event::fake([AchievementUnlocked::class, AchievementRevoked::class]);
    $subject = TestSubject::create(['name' => 'Ada']);

    $outcome = app(AwardAchievement::class)->handle($subject, revocable(), ['current' => 1, 'target' => 1]);

    expect($outcome)->toBe(AwardOutcome::Awarded)
        ->and(AchievementAward::query()->whereNull('revoked_at')->count())->toBe(1);

    Event::assertDispatched(AchievementUnlocked::class, 1);
});

it('soft-revokes a revocable award when the condition lapses, keeping history', function (): void {
    Event::fake([AchievementRevoked::class]);
    $subject = TestSubject::create(['name' => 'Bo']);
    $achievement = revocable();
    $action = app(AwardAchievement::class);

    $action->handle($subject, $achievement, ['current' => 1, 'target' => 1]);   // earns it
    $outcome = $action->handle($subject, $achievement, ['current' => 0, 'target' => 1]); // drops off

    expect($outcome)->toBe(AwardOutcome::Revoked)
        ->and(AchievementAward::query()->count())->toBe(1)               // history row kept
        ->and(AchievementAward::query()->whereNull('revoked_at')->count())->toBe(0); // none active

    Event::assertDispatched(AchievementRevoked::class, 1);
});

it('lets a revocable achievement be re-earned after a revoke', function (): void {
    Event::fake([AchievementUnlocked::class]);
    $subject = TestSubject::create(['name' => 'Cy']);
    $achievement = revocable();
    $action = app(AwardAchievement::class);

    $action->handle($subject, $achievement, ['current' => 1, 'target' => 1]); // earn
    $action->handle($subject, $achievement, ['current' => 0, 'target' => 1]); // lose
    $outcome = $action->handle($subject, $achievement, ['current' => 1, 'target' => 1]); // re-earn

    expect($outcome)->toBe(AwardOutcome::Awarded)
        ->and(AchievementAward::query()->count())->toBe(2)               // revoked + active
        ->and(AchievementAward::query()->whereNull('revoked_at')->count())->toBe(1);

    Event::assertDispatched(AchievementUnlocked::class, 2);
});

it('does not re-award or re-fire while a revocable condition still holds', function (): void {
    Event::fake([AchievementUnlocked::class]);
    $subject = TestSubject::create(['name' => 'Di']);
    $achievement = revocable();
    $action = app(AwardAchievement::class);

    $action->handle($subject, $achievement, ['current' => 1, 'target' => 1]);
    $outcome = $action->handle($subject, $achievement, ['current' => 1, 'target' => 1]);

    expect($outcome)->toBe(AwardOutcome::AlreadyAwarded)
        ->and(AchievementAward::query()->count())->toBe(1);

    Event::assertDispatched(AchievementUnlocked::class, 1);
});

it('never revokes a permanent achievement when its state later changes', function (): void {
    Event::fake([AchievementRevoked::class]);
    $subject = TestSubject::create(['name' => 'Ed']);
    $achievement = permanent();
    $action = app(AwardAchievement::class);

    $action->handle($subject, $achievement, ['current' => 1, 'target' => 1]);   // earns it
    $outcome = $action->handle($subject, $achievement, ['current' => 0, 'target' => 1]); // state drops

    expect($outcome)->toBe(AwardOutcome::AlreadyAwarded)
        ->and(AchievementAward::query()->whereNull('revoked_at')->count())->toBe(1);

    Event::assertNotDispatched(AchievementRevoked::class);
});

it('snapshots context onto the award at award time', function (): void {
    $subject = TestSubject::create(['name' => 'Fi']);

    app(AwardAchievement::class)->handle($subject, permanent(), [
        'current' => 1,
        'target' => 1,
        'context' => ['rank' => 1, 'season' => 3],
    ]);

    expect(AchievementAward::query()->firstOrFail()->context)->toBe(['rank' => 1, 'season' => 3]);
});
