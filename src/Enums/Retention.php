<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Enums;

/**
 * Whether an award, once earned, is kept for life or held only while its
 * condition remains true.
 */
enum Retention: string
{
    /** Earned once, kept forever — even if the underlying state later changes. */
    case Permanent = 'permanent';

    /** Held only while currently true; soft-revoked (history kept) when it lapses. */
    case Revocable = 'revocable';
}
