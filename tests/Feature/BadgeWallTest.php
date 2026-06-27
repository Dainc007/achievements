<?php

declare(strict_types=1);

use Dainc007\Achievements\Filament\Pages\BadgeWall;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

/**
 * Page registration + routing is asserted here (catches wiring/namespace
 * regressions). The page's actual render is exercised in OnSide — see the note
 * in AchievementResourceTest about the Testbench/Livewire render limitation.
 */
beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

it('registers the badge wall page on the panel', function (): void {
    expect(Filament::getPanel('admin')->getPages())
        ->toContain(BadgeWall::class);
});

it('registers a route for the badge wall', function (): void {
    expect(Route::has('filament.admin.pages.badge-wall'))->toBeTrue();
});
