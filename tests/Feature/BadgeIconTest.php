<?php

declare(strict_types=1);

use Dainc007\Achievements\Filament\Support\BadgeIcon;

it('returns a registered icon unchanged', function (): void {
    expect(BadgeIcon::resolve('heroicon-o-trophy'))->toBe('heroicon-o-trophy');
});

it('falls back when the icon name is not registered', function (): void {
    expect(BadgeIcon::resolve('dsada'))->toBe('heroicon-o-trophy');
});

it('falls back when the icon is null or empty', function (): void {
    expect(BadgeIcon::resolve(null))->toBe('heroicon-o-trophy');
    expect(BadgeIcon::resolve(''))->toBe('heroicon-o-trophy');
});

it('honours a configured fallback icon', function (): void {
    config()->set('achievements.fallback_icon', 'heroicon-o-star');

    expect(BadgeIcon::resolve('not-a-real-icon'))->toBe('heroicon-o-star');
});
