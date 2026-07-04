<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Support;

use BladeUI\Icons\Exceptions\SvgNotFound;
use BladeUI\Icons\Factory;
use Throwable;

/**
 * Resolves an achievement's icon name to one that is guaranteed to render.
 *
 * Admin-authored achievements can carry an arbitrary icon string; an
 * unregistered name (e.g. a typo) would otherwise throw {@see SvgNotFound}
 * and take down the whole badge wall. This returns the icon when it resolves,
 * and the configured fallback otherwise, so a bad definition can never 500 the page.
 */
final class BadgeIcon
{
    public static function resolve(?string $icon): string
    {
        $fallback = self::fallback();

        if ($icon === null || $icon === '') {
            return $fallback;
        }

        try {
            app(Factory::class)->svg($icon);

            return $icon;
        } catch (Throwable) {
            return $fallback;
        }
    }

    private static function fallback(): string
    {
        $configured = config('achievements.fallback_icon');

        return is_string($configured) && $configured !== ''
            ? $configured
            : 'heroicon-o-trophy';
    }
}
