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
        if ($icon !== null && $icon !== '' && self::exists($icon)) {
            return $icon;
        }

        return self::fallback();
    }

    /**
     * Whether an icon name resolves to a registered SVG. Used both to render
     * safely and to validate the icon field before an achievement is saved.
     */
    public static function exists(string $icon): bool
    {
        try {
            app(Factory::class)->svg($icon);

            return true;
        } catch (Throwable) {
            return false;
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
