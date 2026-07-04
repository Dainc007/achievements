<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Pages;

use BackedEnum;
use Dainc007\Achievements\Domain\Awardable;
use Dainc007\Achievements\Support\Badge;
use Dainc007\Achievements\Support\BadgeCollection;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Livewire\WithPagination;

/**
 * App-panel page showing the authenticated subject's earned + in-progress
 * badges, paginated + searchable + sortable so a large catalogue renders one
 * page of cards at a time. Each card is rendered by the view the
 * BadgeRendererRegistry maps to its category, so apps can restyle per category.
 */
final class BadgeWall extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected string $view = 'achievements::filament.pages.badge-wall';

    public string $search = '';

    public string $sort = 'status';

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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Badge>
     */
    public function getBadges(): LengthAwarePaginator
    {
        $user = Filament::auth()->user();

        if ($user instanceof Model && $user instanceof Awardable) {
            return BadgeCollection::paginateFor(
                $user,
                perPage: 12,
                search: trim($this->search),
                sort: $this->sort,
            );
        }

        return new Paginator([], 0, 12);
    }

    /**
     * @return array<string, string>
     */
    public function sortOptions(): array
    {
        return [
            'status' => __('achievements::achievements.sort.status'),
            'tier' => __('achievements::achievements.sort.tier'),
            'name' => __('achievements::achievements.sort.name'),
            'newest' => __('achievements::achievements.sort.newest'),
        ];
    }
}
