<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Tests\Fixtures;

use Dainc007\Achievements\Domain\Awardable;
use Illuminate\Database\Eloquent\Model;

/**
 * A minimal Eloquent model that can earn achievements, used to exercise the
 * polymorphic subject path in feature tests.
 */
final class TestSubject extends Model implements Awardable
{
    protected $guarded = [];

    public function awardableKey(): string
    {
        return 'test_subject:'.$this->getKey();
    }
}
