<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Resources\AchievementResource\Pages;

use Dainc007\Achievements\Filament\Resources\AchievementResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAchievement extends CreateRecord
{
    protected static string $resource = AchievementResource::class;
}
