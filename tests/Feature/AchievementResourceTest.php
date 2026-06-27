<?php

declare(strict_types=1);

use Dainc007\Achievements\Filament\Resources\AchievementResource;
use Dainc007\Achievements\Models\Achievement;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

/**
 * The admin layer is asserted at the wiring level: the plugin registers the
 * resource on the panel, the resource is configured correctly, and its routes
 * exist. These catch namespace/registration/config regressions.
 *
 * Page rendering and form interaction (fillForm → create) are NOT asserted here:
 * Livewire v4's per-component error-bag store does not resolve under Orchestra
 * Testbench, so rendering a Filament page 500s in this harness only (it works in
 * a real app). Those flows are exercised when the plugin is wired into OnSide
 * (the integration milestone), alongside filacheck and manual verification.
 * Model persistence/casts/uniqueness are covered by AchievementModelTest.
 */
beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

it('registers the achievement resource on the panel via the plugin', function (): void {
    expect(Filament::getPanel('admin')->getResources())
        ->toContain(AchievementResource::class);
});

it('points the resource at the Achievement model', function (): void {
    expect(AchievementResource::getModel())->toBe(Achievement::class);
});

it('exposes index, create and edit pages', function (): void {
    expect(array_keys(AchievementResource::getPages()))
        ->toBe(['index', 'create', 'edit']);
});

it('registers panel routes for the resource', function (): void {
    expect(Route::has('filament.admin.resources.achievements.index'))->toBeTrue()
        ->and(Route::has('filament.admin.resources.achievements.create'))->toBeTrue()
        ->and(Route::has('filament.admin.resources.achievements.edit'))->toBeTrue();
});
