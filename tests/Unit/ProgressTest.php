<?php

declare(strict_types=1);

use Dainc007\Achievements\Domain\Progress;

it('is not complete below the target', function (): void {
    expect(new Progress(current: 3, target: 10))
        ->isComplete()->toBeFalse();
});

it('is complete when current reaches the target', function (): void {
    expect(new Progress(current: 10, target: 10))
        ->isComplete()->toBeTrue();
});

it('is complete when current exceeds the target', function (): void {
    expect(new Progress(current: 12, target: 10))
        ->isComplete()->toBeTrue();
});

it('computes a percentage toward the target', function (): void {
    expect(new Progress(current: 5, target: 20))
        ->percent()->toBe(25.0);
});

it('caps the percentage at 100 when over-achieved', function (): void {
    expect(new Progress(current: 30, target: 20))
        ->percent()->toBe(100.0);
});

it('returns 0 percent for a zero target without dividing by zero', function (): void {
    expect(new Progress(current: 0, target: 0))
        ->percent()->toBe(0.0)
        ->and(new Progress(current: 0, target: 0)->isComplete())->toBeTrue();
});

it('exposes current and target as readonly state', function (): void {
    $progress = new Progress(current: 4, target: 8);

    expect($progress->current)->toBe(4)
        ->and($progress->target)->toBe(8);
});
