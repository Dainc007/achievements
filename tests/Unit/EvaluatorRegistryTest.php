<?php

declare(strict_types=1);

use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Domain\Evaluator;
use Dainc007\Achievements\Domain\Progress;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Dainc007\Achievements\Support\UnknownEvaluator;

function fakeEvaluator(): Evaluator
{
    return new class implements Evaluator
    {
        public function progress(Awardable $subject, array $context): Progress
        {
            return new Progress(current: 1, target: 1);
        }
    };
}

it('registers and retrieves an evaluator by type', function (): void {
    $evaluator = fakeEvaluator();

    $registry = (new EvaluatorRegistry)->register('stat_threshold', $evaluator);

    expect($registry->get('stat_threshold'))->toBe($evaluator)
        ->and($registry->has('stat_threshold'))->toBeTrue();
});

it('reports unknown types as absent', function (): void {
    expect((new EvaluatorRegistry)->has('missing'))->toBeFalse();
});

it('throws a typed exception when retrieving an unknown type', function (): void {
    (new EvaluatorRegistry)->get('missing');
})->throws(UnknownEvaluator::class, 'missing');

it('overwrites a previously registered type', function (): void {
    $first = fakeEvaluator();
    $second = fakeEvaluator();

    $registry = (new EvaluatorRegistry)
        ->register('stat_threshold', $first)
        ->register('stat_threshold', $second);

    expect($registry->get('stat_threshold'))->toBe($second);
});

it('lists registered type keys for the type picker', function (): void {
    $registry = (new EvaluatorRegistry)
        ->register('stat_threshold', fakeEvaluator())
        ->register('streak', fakeEvaluator());

    expect($registry->keys())->toBe(['stat_threshold', 'streak']);
});

it('lists no keys when nothing is registered', function (): void {
    expect((new EvaluatorRegistry)->keys())->toBe([]);
});
