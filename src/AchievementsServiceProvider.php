<?php

declare(strict_types=1);

namespace Dainc007\Achievements;

use Dainc007\Achievements\Commands\RecalculateAchievements;
use Dainc007\Achievements\Commands\TickAchievements;
use Dainc007\Achievements\Filament\Support\BadgeRendererRegistry;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AchievementsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('achievements')
            ->hasConfigFile()
            ->hasMigrations([
                '2025_01_01_000001_create_achievements_table',
                '2025_01_01_000002_create_achievement_awards_table',
                '2025_01_01_000003_create_achievement_progress_table',
                '2025_01_01_000004_add_image_to_achievements_table',
                '2025_01_01_000005_make_achievement_text_translatable',
            ])
            ->runsMigrations()
            ->hasCommands([
                RecalculateAchievements::class,
                TickAchievements::class,
            ])
            ->hasViews()
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        // Shared registries; the consuming app populates them in its own provider
        // (evaluators for the engine, per-category badge views for the UI).
        $this->app->singleton(EvaluatorRegistry::class, fn (): EvaluatorRegistry => new EvaluatorRegistry);
        $this->app->singleton(BadgeRendererRegistry::class, fn (): BadgeRendererRegistry => new BadgeRendererRegistry);
    }
}
