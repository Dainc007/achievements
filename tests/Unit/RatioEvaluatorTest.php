<?php

declare(strict_types=1);

use Dainc007\Achievements\Evaluators\RatioEvaluator;

// subjectStub() and statResolverStub() are declared in StatThresholdEvaluatorTest.

it('reports the percentage once the minimum sample is met', function (): void {
    $evaluator = new RatioEvaluator(statResolverStub(['games_won' => 80, 'games_played' => 100]));

    $progress = $evaluator->progress(subjectStub(), [
        'config' => ['stat' => 'games_won', 'per' => 'games_played', 'target' => 70, 'min' => 100],
    ]);

    expect($progress->current)->toBe(80)
        ->and($progress->target)->toBe(70)
        ->and($progress->isComplete())->toBeTrue();
});

it('is not eligible below the minimum sample size', function (): void {
    $evaluator = new RatioEvaluator(statResolverStub(['games_won' => 5, 'games_played' => 5]));

    $progress = $evaluator->progress(subjectStub(), [
        'config' => ['stat' => 'games_won', 'per' => 'games_played', 'target' => 70, 'min' => 100],
    ]);

    // 100% win rate but only 5 games — must not qualify.
    expect($progress->current)->toBe(0)
        ->and($progress->isComplete())->toBeFalse();
});

it('floors the percentage and stays below target when under it', function (): void {
    $evaluator = new RatioEvaluator(statResolverStub(['games_won' => 69, 'games_played' => 100]));

    $progress = $evaluator->progress(subjectStub(), [
        'config' => ['stat' => 'games_won', 'per' => 'games_played', 'target' => 70, 'min' => 100],
    ]);

    expect($progress->current)->toBe(69)
        ->and($progress->isComplete())->toBeFalse();
});

it('treats a zero denominator as no progress', function (): void {
    $evaluator = new RatioEvaluator(statResolverStub(['games_won' => 0, 'games_played' => 0]));

    $progress = $evaluator->progress(subjectStub(), [
        'config' => ['stat' => 'games_won', 'per' => 'games_played', 'target' => 70, 'min' => 0],
    ]);

    expect($progress->current)->toBe(0);
});

it('rejects config missing stat or per keys', function (): void {
    new RatioEvaluator(statResolverStub([]))
        ->progress(subjectStub(), ['config' => ['stat' => 'games_won', 'target' => 70]]);
})->throws(InvalidArgumentException::class);
