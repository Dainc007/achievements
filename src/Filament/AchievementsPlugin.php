<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament;

use Dainc007\Achievements\Filament\Pages\BadgeWall;
use Dainc007\Achievements\Filament\Resources\AchievementResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Registers the achievements UI on a Filament panel:
 *
 *     // admin panel — authoring only
 *     $panel->plugin(AchievementsPlugin::make()->badgeWall(false));
 *
 *     // app panel — badge wall only
 *     $panel->plugin(AchievementsPlugin::make()->resources(false));
 *
 * Both are on by default; toggle per panel.
 */
final class AchievementsPlugin implements Plugin
{
    private bool $registersResources = true;

    private bool $registersBadgeWall = true;

    public function getId(): string
    {
        return 'achievements';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function resources(bool $condition = true): static
    {
        $this->registersResources = $condition;

        return $this;
    }

    public function badgeWall(bool $condition = true): static
    {
        $this->registersBadgeWall = $condition;

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->registersResources) {
            $panel->resources([
                AchievementResource::class,
            ]);
        }

        if ($this->registersBadgeWall) {
            $panel->pages([
                BadgeWall::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
