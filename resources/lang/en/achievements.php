<?php

declare(strict_types=1);

return [
    'nav_label' => 'Achievements',
    'title' => 'Achievements',
    'model_label' => 'achievement',
    'plural_label' => 'Achievements',
    'earned' => 'Earned',
    'locked' => 'Locked',
    'empty' => 'No achievements yet — start playing to earn your first badge.',
    'no_results' => 'No achievements match your search.',
    'search_placeholder' => 'Search achievements…',

    'sort' => [
        'status' => 'Earned first',
        'tier' => 'Tier',
        'name' => 'Name (A–Z)',
        'newest' => 'Newest',
    ],

    'summary' => [
        'earned' => 'Earned',
        'in_progress' => 'In progress',
        'points' => 'Points',
        'completion' => ':percent% complete',
    ],

    'tiers' => [
        'bronze' => 'Bronze',
        'silver' => 'Silver',
        'gold' => 'Gold',
        'legendary' => 'Legendary',
    ],

    'retentions' => [
        'permanent' => 'Permanent',
        'revocable' => 'Revocable',
    ],

    'types' => [
        'stat_threshold' => 'Stat threshold',
        'accumulator' => 'Accumulator',
        'streak' => 'Streak',
    ],

    'table' => [
        'key' => 'Key',
        'name' => 'Name',
        'type' => 'Type',
        'tier' => 'Tier',
        'retention' => 'Retention',
        'is_progressive' => 'Progressive',
        'is_active' => 'Active',
    ],

    'form' => [
        'section_definition' => 'Definition',
        'section_terms' => 'When it is awarded',
        'terms_hint' => 'Pick a type above first — its settings will appear here.',
        'section_presentation' => 'Appearance',
        'section_behaviour' => 'Behaviour',

        'key' => 'Key',
        'key_help' => 'Stable identifier, e.g. "goal_machine". Must be unique.',
        'name' => 'Name',
        'description' => 'Description',
        'type' => 'How it is awarded',
        'type_help' => 'How progress is measured: "Stat threshold" awards when a counter reaches a value (e.g. 10 contracts).',
        'category' => 'Category',

        'stat' => 'Stat',
        'stat_help' => 'Which counter this achievement watches.',
        'stat_placeholder' => 'Select a stat',
        'target' => 'Target',
        'target_help' => 'Award once the stat reaches this number.',
        'config' => 'Configuration',
        'config_help' => 'Settings for this evaluator type.',

        'badge_source' => 'Badge source',
        'badge_source_icon' => 'Icon',
        'badge_source_image' => 'Custom image',

        'icon' => 'Icon',
        'icon_help' => 'A Heroicon name, e.g. "heroicon-o-trophy". Leave empty for the default.',
        'icon_invalid' => 'That icon does not exist. Use a valid Heroicon name like "heroicon-o-trophy".',
        'image' => 'Custom image',
        'image_help' => 'Optional. Takes precedence over the icon when set.',
        'tier' => 'Tier',
        'is_progressive' => 'Show a progress bar',
        'points' => 'Points',
        'points_help' => 'Reserved — not yet wired to a leaderboard.',

        'retention' => 'Retention',
        'retention_help' => 'Permanent: kept for life. Revocable: held only while the condition is true.',
        'is_active' => 'Active',
    ],
];
