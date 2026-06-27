<?php

declare(strict_types=1);

use Dainc007\Achievements\Filament\Support\BadgeRendererRegistry;

it('returns the default view for an unmapped category', function (): void {
    expect((new BadgeRendererRegistry)->viewFor('anything'))
        ->toBe('achievements::badges.default');
});

it('returns a registered view for its category', function (): void {
    $registry = (new BadgeRendererRegistry)->register('legendary', 'app.badges.legendary');

    expect($registry->viewFor('legendary'))->toBe('app.badges.legendary')
        ->and($registry->viewFor('routine'))->toBe('achievements::badges.default');
});

it('allows overriding the default view', function (): void {
    $registry = (new BadgeRendererRegistry)->default('app.badges.card');

    expect($registry->viewFor(null))->toBe('app.badges.card');
});
