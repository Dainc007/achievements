<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Resources\AchievementResource\Pages;

use Dainc007\Achievements\Filament\Resources\AchievementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
