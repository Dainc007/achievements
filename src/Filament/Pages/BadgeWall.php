<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Pages;

use BackedEnum;
use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Support\Badge;
use Dainc007\Achievements\Support\BadgeCollection;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * App-panel page showing the authenticated subject's earned + in-progress
 * badges. Each card is rendered by the view the BadgeRendererRegistry maps to
 * its category, so apps can restyle per category without touching this page.
 */
final class BadgeWall extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected string $view = 'achievements::filament.pages.badge-wall';

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('achievements::achievements.nav_label');
    }

    #[\Override]
    public function getTitle(): string
    {
        return __('achievements::achievements.title');
    }

    /**
     * @return Collection<int, Badge>
     */
    public function getBadges(): Collection
    {
        $user = Filament::auth()->user();

        if ($user instanceof Model && $user instanceof Awardable) {
            return BadgeCollection::for($user);
        }

        return collect();
    }
}
