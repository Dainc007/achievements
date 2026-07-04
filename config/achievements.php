<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Badge image disk
    |--------------------------------------------------------------------------
    |
    | Filesystem disk used to store and serve custom badge images uploaded via
    | the admin UI (the achievements.image column holds a path on this disk).
    | Point it at a publicly accessible disk in production (e.g. a public S3
    | disk) so images survive deploys and are reachable by the badge wall.
    |
    */

    'image_disk' => env('ACHIEVEMENTS_IMAGE_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Fallback icon
    |--------------------------------------------------------------------------
    |
    | Rendered when an achievement has no image and either no icon or an icon
    | that is not registered with Blade Icons. Guarantees the badge wall never
    | fails on a missing or misconfigured icon.
    |
    */

    'fallback_icon' => env('ACHIEVEMENTS_FALLBACK_ICON', 'heroicon-o-trophy'),

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Locales an admin can author name/description in. The create/edit form
    | renders one tab per locale, and the model resolves the active locale
    | (falling back to app.fallback_locale) when a badge is displayed. Set via
    | ACHIEVEMENTS_LOCALES="pl,en" or override this config in the consuming app.
    |
    */

    'locales' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ACHIEVEMENTS_LOCALES', (string) (config('app.fallback_locale') ?: 'en')))
    ))),

];
