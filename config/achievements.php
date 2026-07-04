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

];
