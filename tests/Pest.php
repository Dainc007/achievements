<?php

declare(strict_types=1);

use Dainc007\Achievements\Tests\TestCase;

// Pure-PHP domain tests need no Laravel app — keep them framework-free for speed.
pest()->group('unit')->in('Unit');

// Feature tests boot Laravel via Orchestra Testbench.
pest()->extend(TestCase::class)->group('feature')->in('Feature');
