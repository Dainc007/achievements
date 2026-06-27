<?php

declare(strict_types=1);

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Evaluators\AccumulatorEvaluator;

function accumulatorSubject(): Awardable
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
function accumulatorContext(array $overrides = []): array
{
    return array_replace([
        'qualifies' => true,
        'date' => '2026-06-10',
        'config' => ['target' => 100],
        'progress' => ['current' => 0, 'meta' => []],
    ], $overrides);
}

it('counts the first qualifying day as 1', function (): void {
    $progress = (new AccumulatorEvaluator)->progress(accumulatorSubject(), accumulatorContext());

    expect($progress->current)->toBe(1)
        ->and($progress->target)->toBe(100)
        ->and($progress->meta['last_counted_on'])->toBe('2026-06-10');
});

it('increments on a later qualifying day', function (): void {
    $progress = (new AccumulatorEvaluator)->progress(accumulatorSubject(), accumulatorContext([
        'progress' => ['current' => 5, 'meta' => ['last_counted_on' => '2026-06-08']],
    ]));

    expect($progress->current)->toBe(6);
});

it('does not count the same day twice (idempotent)', function (): void {
    $progress = (new AccumulatorEvaluator)->progress(accumulatorSubject(), accumulatorContext([
        'progress' => ['current' => 5, 'meta' => ['last_counted_on' => '2026-06-10']],
    ]));

    expect($progress->current)->toBe(5);
});

it('never resets and does not increment on a non-qualifying day', function (): void {
    $progress = (new AccumulatorEvaluator)->progress(accumulatorSubject(), accumulatorContext([
        'qualifies' => false,
        'progress' => ['current' => 7, 'meta' => ['last_counted_on' => '2026-06-08']],
    ]));

    expect($progress->current)->toBe(7);
});
