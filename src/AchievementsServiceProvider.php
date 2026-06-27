<?php

declare(strict_types=1);

namespace Dainc007\Achievements;

use Dainc007\Achievements\Commands\RecalculateAchievements;
use Dainc007\Achievements\Commands\TickAchievements;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AchievementsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('achievements')
            ->hasMigrations([
                'create_achievements_table',
                'create_achievement_awards_table',
                'create_achievement_progress_table',
            ])
            ->hasCommands([
                RecalculateAchievements::class,
                TickAchievements::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // The registry is shared; the consuming app populates it with evaluators
        // (config-driven + bespoke) in its own service provider.
        $this->app->singleton(EvaluatorRegistry::class, fn (): EvaluatorRegistry => new EvaluatorRegistry);
    }
}
