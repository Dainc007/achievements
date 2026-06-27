<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Resources\AchievementResource\Pages;

use Dainc007\Achievements\Filament\Resources\AchievementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditAchievement extends EditRecord
{
    protected static string $resource = AchievementResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
