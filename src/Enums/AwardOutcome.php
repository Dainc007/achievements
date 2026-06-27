<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Enums;

/**
 * The result of evaluating one achievement for one subject.
 */
enum AwardOutcome: string
{
    /** A new award was created and AchievementUnlocked fired. */
    case Awarded = 'awarded';

    /** Progress advanced but the target is not yet met. */
    case Progressed = 'progressed';

    /** The subject already held this award; nothing changed. */
    case AlreadyAwarded = 'already_awarded';
}
