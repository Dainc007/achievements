<?php

declare(strict_types=1);

use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Support\Badge;

/**
 * Renders the default badge view directly (not the Livewire page — see the note
 * in BadgeWallTest about the Testbench render limitation) to prove a bad or
 * missing icon and a custom image are handled without throwing.
 */
function renderDefaultBadge(Achievement $achievement): string
{
    $badge = new Badge($achievement, earned: true, current: 1, target: 1);

    return view('achievements::badges.default', ['badge' => $badge])->render();
}

it('renders without throwing when the icon is not a real icon', function (): void {
    $achievement = Achievement::create([
        'key' => 'bad_icon',
        'name' => 'Bad Icon',
        'type' => 'stat_threshold',
        'icon' => 'dsada',
    ]);

    $html = renderDefaultBadge($achievement);

    expect($html)->toContain('Bad Icon');
});

it('renders without throwing when there is no icon at all', function (): void {
    $achievement = Achievement::create([
        'key' => 'no_icon',
        'name' => 'No Icon',
        'type' => 'stat_threshold',
        'icon' => null,
    ]);

    $html = renderDefaultBadge($achievement);

    expect($html)->toContain('No Icon');
});

it('renders a custom image when one is set, instead of an icon', function (): void {
    $achievement = Achievement::create([
        'key' => 'with_image',
        'name' => 'With Image',
        'type' => 'stat_threshold',
        'icon' => 'dsada',
        'image' => 'badges/custom.png',
    ]);

    $html = renderDefaultBadge($achievement);

    expect($html)
        ->toContain('<img')
        ->toContain('badges/custom.png');
});
