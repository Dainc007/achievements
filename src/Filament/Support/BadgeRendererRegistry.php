<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Support;

/**
 * Maps an achievement `category` to the Blade view that renders its badge card,
 * with a sensible default. Consuming apps customise the look by registering a
 * view per category (or overriding the default) — no backend changes:
 *
 *     app(BadgeRendererRegistry::class)
 *         ->register('legendary', 'app.badges.legendary')
 *         ->default('app.badges.card');
 */
final class BadgeRendererRegistry
{
    /** @var array<string, string> */
    private array $views = [];

    private string $defaultView = 'achievements::badges.default';

    public function register(string $category, string $view): self
    {
        $this->views[$category] = $view;

        return $this;
    }

    public function default(string $view): self
    {
        $this->defaultView = $view;

        return $this;
    }

    public function viewFor(?string $category): string
    {
        return $this->views[$category] ?? $this->defaultView;
    }
}
