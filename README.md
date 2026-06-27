# dainc007/achievements

A reusable, event-driven **achievement / badge engine for Laravel**, with an optional
**Filament v5** layer. Private / work-in-progress.

> **Status: v1 feature-complete.** Engine + Filament layer built and tested (72 tests,
> `composer quality` green). Integrated into the OnSide app (`wk`) on branch
> `feat/achievements-integration`. Not yet published to a registry — see *Deployment*.

---

## How it works (mental model)

```
Something happens  ─►  an Evaluator checks "does this qualify, how far along?"  ─►  award + record progress
(event or backfill)    (one strategy per achievement type)                          (idempotent, once only)
```

Two layers, one package, clean internal split:

- **Engine** (`src/`, no Filament): models, evaluators, awarding, commands.
- **Filament layer** (`src/Filament/`): admin CRUD + app-panel badge wall. Activates only
  when Filament is installed (`filament/filament` is `require-dev` + `suggest`).

This is a **Laravel package** (Eloquent/migrations/commands), not framework-agnostic. The
pure-PHP classes in `src/Domain` are an internal, testable seam.

## Core concepts

- **Achievement** (`Models\Achievement`): a definition — `key`, `name`, `type` (evaluator),
  `config` (JSON), `tier`, `category`, `retention`, `is_progressive`, `points` (reserved).
- **Evaluator** (`Domain\Evaluator`): decides progress. Shipped:
  - `StatThresholdEvaluator` — config-driven `{stat, target}` ("score 50 goals"). No code per achievement.
  - `StreakEvaluator` — consecutive qualifying days (resets on a miss). Time-based.
  - `AccumulatorEvaluator` — +1 per qualifying day (never resets). Time-based.
  - Register your own in the `EvaluatorRegistry` (keyed by the achievement's `type`).
- **Retention** (`Enums\Retention`): `permanent` (kept for life) vs `revocable` (held only
  while true; soft-revoked via `revoked_at`, history kept, re-earnable).
- **Subjects** are polymorphic (User, Team, …) — implement `Domain\Awardable` (or use the
  `Concerns\HasAchievements` trait).

## Consuming app wiring (what OnSide does)

1. `composer require dainc007/achievements` (currently a local path repo).
2. `php artisan migrate` (three tables; runs automatically).
3. Subject model: `implements Awardable` + `use HasAchievements`.
4. Bind the resolvers the app must provide:
   - `StatResolver` — current value of a named stat for a subject.
   - `SubjectResolver` — `@template`d query of subjects to backfill (`Builder<YourModel>`).
   - `ConditionResolver` — "does this subject qualify today?" (only for time-based achievements).
5. Register evaluators into the `EvaluatorRegistry` (e.g. `extend()` in a service provider).
6. Register the Filament plugin per panel: `AchievementsPlugin::make()->badgeWall(false)`
   (admin = CRUD) and `->resources(false)` (app = badge wall).
7. Author achievements via the admin CRUD or a seeder.

## Awarding

- **Event-driven:** call `AwardAchievement::handle($subject, $achievement, $context)` from a
  listener for instant awards. Idempotent (unique constraint + existing-award short-circuit).
- **Backfill:** `php artisan achievements:recalculate {achievement?}` — awards existing
  subjects who already qualify. On-demand, safe to re-run, skips already-awarded (permanent).
- **Time-based:** `php artisan achievements:tick {--date=}` — daily, advances streak/
  accumulator achievements; idempotent per day.

## Customising the badge UI

`Filament\Support\BadgeRendererRegistry` maps a `category` → Blade view, with a default
(`achievements::badges.default`). Override per category or replace the default with no
backend change.

## Testing & quality

- `composer test` (Pest 4 + Orchestra Testbench).
- `composer quality` (pint --test + rector --dry-run + phpstan lvl 6 + pest).
- **Known limitation:** Filament *page* render can't be tested under Testbench (Livewire v4
  error-bag store), so Filament tests assert registration/routes; real render is verified in
  the consuming app.

## Deployment

The package is consumed via a **local path repo** in OnSide, which won't exist on Laravel
Cloud. **Before deploying: publish to a private VCS/Packagist repo** and require it normally.

## Roadmap / next steps

- Publish to a private registry (deployment blocker).
- OnSide: wire live events (e.g. contract signed → `AwardAchievement`) for instant awards.
- Real `ConditionResolver` + scheduled `tick` for "be #1"-style time-based achievements.
- `points` → Leaderboard integration (column exists, currently inert).
- `Team` as a second subject type.

Build history is in the conventional-commit log (`git log`). The full design rationale lives
in the OnSide repo at `docs/achievements-package-plan.md` (branch `docs/achievements-plan`).
