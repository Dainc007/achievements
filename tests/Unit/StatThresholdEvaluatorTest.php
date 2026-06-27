<?php

declare(strict_types=1);

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\StatResolver;
use Dainc007\Achievements\Evaluators\StatThresholdEvaluator;

function subjectStub(string $key = 'user:1'): Awardable
{
    return new class($key) implements Awardable
    {
        public function __construct(private string $key) {}

        public function awardableKey(): string
        {
            return $this->key;
        }
    };
}

/**
 * @param  array<string, int>  $stats
 */
function statResolverStub(array $stats): StatResolver
{
    return new class($stats) implements StatResolver
    {
        /** @param array<string, int> $stats */
        public function __construct(private array $stats) {}

        public function value(Awardable $subject, string $statKey, array $context): int
        {
            return $this->stats[$statKey] ?? 0;
        }
    };
}

it('reports progress toward the configured stat target', function (): void {
    $evaluator = new StatThresholdEvaluator(statResolverStub(['goals' => 30]));

    $progress = $evaluator->progress(subjectStub(), [
        'config' => ['stat' => 'goals', 'target' => 50],
    ]);

    expect($progress->current)->toBe(30)
        ->and($progress->target)->toBe(50)
        ->and($progress->isComplete())->toBeFalse();
});

it('is complete once the resolved stat meets the target', function (): void {
    $evaluator = new StatThresholdEvaluator(statResolverStub(['matches' => 100]));

    $progress = $evaluator->progress(subjectStub(), [
        'config' => ['stat' => 'matches', 'target' => 100],
    ]);

    expect($progress->isComplete())->toBeTrue();
});

it('treats a never-recorded stat as zero progress', function (): void {
    $evaluator = new StatThresholdEvaluator(statResolverStub([]));

    $progress = $evaluator->progress(subjectStub(), [
        'config' => ['stat' => 'clean_sheets', 'target' => 10],
    ]);

    expect($progress->current)->toBe(0)
        ->and($progress->isComplete())->toBeFalse();
});

it('rejects config missing a stat key', function (): void {
    (new StatThresholdEvaluator(statResolverStub([])))
        ->progress(subjectStub(), ['config' => ['target' => 10]]);
})->throws(InvalidArgumentException::class);
