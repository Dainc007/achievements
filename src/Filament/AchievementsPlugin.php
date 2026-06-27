<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament;

use Dainc007\Achievements\Filament\Resources\AchievementResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Registers the achievements admin UI on a Filament panel:
 *
 *     $panel->plugin(AchievementsPlugin::make());
 *
 * The badge-wall widget for the app panel is registered separately by the
 * consuming app (it chooses where it appears).
 */
final class AchievementsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'achievements';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            AchievementResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
