<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Domain;

/**
 * Declares the stats a consuming app exposes for config-driven achievements.
 *
 * The admin form reads this to offer a "stat" dropdown (label => human name)
 * instead of making authors type raw stat keys. Bind an implementation in the
 * app's service provider; when none is bound the form falls back to free text.
 * Keys returned here must be the same keys the app's {@see StatResolver} understands.
 */
interface StatCatalog
{
    /**
     * @return array<string, string> stat key => human-readable label
     */
    public function options(): array;
}
