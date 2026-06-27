<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Support;

use InvalidArgumentException;

final class UnknownEvaluator extends InvalidArgumentException
{
    public static function forType(string $type): self
    {
        return new self("No evaluator registered for achievement type [{$type}].");
    }
}
