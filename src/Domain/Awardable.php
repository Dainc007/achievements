<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Domain;

/**
 * Something that can earn an achievement — a User, a Team, etc.
 *
 * Consuming models implement this (typically via the HasAchievements trait)
 * so the engine can identify a subject without depending on any concrete model.
 */
interface Awardable
{
    /**
     * A stable, unique identifier for this subject, e.g. "user:42".
     */
    public function awardableKey(): string;
}
