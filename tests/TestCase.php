<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Package service providers to load into the test application.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            // AchievementsServiceProvider::class — added in milestone 1.
        ];
    }
}
