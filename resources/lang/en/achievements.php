<?php

declare(strict_types=1);

return [
    'nav_label' => 'Achievements',
    'title' => 'Achievements',
    'earned' => 'Earned',
    'locked' => 'Locked',
    'empty' => 'No achievements yet — start playing to earn your first badge.',

    'form' => [
        'section_definition' => 'Definition',
        'section_terms' => 'When it is awarded',
        'section_presentation' => 'How it looks',
        'section_behaviour' => 'Behaviour',

        'key' => 'Key',
        'key_help' => 'Stable identifier, e.g. "goal_machine". Must be unique.',
        'name' => 'Name',
        'description' => 'Description',
        'type' => 'Type',
        'type_help' => 'How progress is measured.',
        'category' => 'Category',

        'stat' => 'Stat',
        'stat_help' => 'Which counter this achievement watches.',
        'stat_placeholder' => 'Select a stat',
        'target' => 'Target',
        'target_help' => 'Award once the stat reaches this number.',
        'config' => 'Configuration',
        'config_help' => 'Settings for this evaluator type.',

        'icon' => 'Icon',
        'icon_help' => 'A Heroicon name, e.g. "heroicon-o-trophy". Leave empty for the default, or upload an image below.',
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
