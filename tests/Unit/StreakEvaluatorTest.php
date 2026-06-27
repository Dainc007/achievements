<?php

declare(strict_types=1);

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Evaluators\StreakEvaluator;

function streakSubject(): Awardable
{
    return new readonly class implements Awardable
    {
        public function awardableKey(): string
        {
            return 'user:1';
        }
    };
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function streakContext(array $overrides = []): array
{
    return array_replace([
        'qualifies' => true,
        'date' => '2026-06-10',
        'config' => ['target' => 10],
        'progress' => ['current' => 0, 'meta' => []],
    ], $overrides);
}

it('starts a streak at 1 on the first qualifying day', function (): void {
    $progress = (new StreakEvaluator)->progress(streakSubject(), streakContext());

    expect($progress->current)->toBe(1)
        ->and($progress->target)->toBe(10)
        ->and($progress->meta['last_counted_on'])->toBe('2026-06-10');
});

it('increments the streak when yesterday also qualified', function (): void {
    $progress = (new StreakEvaluator)->progress(streakSubject(), streakContext([
        'progress' => ['current' => 3, 'meta' => ['last_counted_on' => '2026-06-09']],
    ]));

    expect($progress->current)->toBe(4);
});

it('does not advance twice on the same day (idempotent)', function (): void {
    $progress = (new StreakEvaluator)->progress(streakSubject(), streakContext([
        'progress' => ['current' => 3, 'meta' => ['last_counted_on' => '2026-06-10']],
    ]));

    expect($progress->current)->toBe(3);
});

it('resets the streak to 1 after a gap', function (): void {
    $progress = (new StreakEvaluator)->progress(streakSubject(), streakContext([
        'progress' => ['current' => 5, 'meta' => ['last_counted_on' => '2026-06-07']],
    ]));

    expect($progress->current)->toBe(1);
});

it('resets the streak to 0 on a non-qualifying day', function (): void {
    $progress = (new StreakEvaluator)->progress(streakSubject(), streakContext([
        'qualifies' => false,
        'progress' => ['current' => 8, 'meta' => ['last_counted_on' => '2026-06-09']],
    ]));

    expect($progress->current)->toBe(0);
});
