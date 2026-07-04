<?php

declare(strict_types=1);

return [
    'nav_label' => 'Osiągnięcia',
    'title' => 'Osiągnięcia',
    'model_label' => 'osiągnięcie',
    'plural_label' => 'Osiągnięcia',
    'earned' => 'Zdobyto',
    'locked' => 'Zablokowane',
    'empty' => 'Brak osiągnięć — zacznij grać, aby zdobyć pierwszą odznakę.',

    'summary' => [
        'earned' => 'Zdobyte',
        'in_progress' => 'W trakcie',
        'points' => 'Punkty',
        'completion' => 'Ukończono :percent%',
    ],

    'tiers' => [
        'bronze' => 'Brąz',
        'silver' => 'Srebro',
        'gold' => 'Złoto',
        'legendary' => 'Legenda',
    ],

    'retentions' => [
        'permanent' => 'Trwałe',
        'revocable' => 'Odwoływalne',
    ],

    'types' => [
        'stat_threshold' => 'Próg statystyki',
        'accumulator' => 'Akumulator',
        'streak' => 'Seria',
    ],

    'table' => [
        'key' => 'Klucz',
        'name' => 'Nazwa',
        'type' => 'Typ',
        'tier' => 'Poziom',
        'retention' => 'Trwałość',
        'is_progressive' => 'Progresywne',
        'is_active' => 'Aktywne',
    ],

    'form' => [
        'section_definition' => 'Definicja',
        'section_terms' => 'Kiedy jest przyznawane',
        'terms_hint' => 'Najpierw wybierz typ powyżej — pojawią się tu jego ustawienia.',
        'section_presentation' => 'Jak wygląda',
        'section_behaviour' => 'Zachowanie',

        'key' => 'Klucz',
        'key_help' => 'Stały identyfikator, np. „goal_machine". Musi być unikalny.',
        'name' => 'Nazwa',
        'description' => 'Opis',
        'type' => 'Typ',
        'type_help' => 'Sposób mierzenia postępu.',
        'category' => 'Kategoria',

        'stat' => 'Statystyka',
        'stat_help' => 'Który licznik śledzi to osiągnięcie.',
        'stat_placeholder' => 'Wybierz statystykę',
        'target' => 'Cel',
        'target_help' => 'Przyznaj, gdy statystyka osiągnie tę wartość.',
        'config' => 'Konfiguracja',
        'config_help' => 'Ustawienia dla tego typu ewaluatora.',

        'icon' => 'Ikona',
        'icon_help' => 'Nazwa ikony Heroicon, np. „heroicon-o-trophy". Zostaw puste, aby użyć domyślnej, lub wgraj obraz poniżej.',
        'icon_invalid' => 'Taka ikona nie istnieje. Użyj prawidłowej nazwy Heroicon, np. „heroicon-o-trophy".',
        'image' => 'Własny obraz',
        'image_help' => 'Opcjonalne. Ma pierwszeństwo przed ikoną, gdy ustawione.',
        'tier' => 'Poziom',
        'is_progressive' => 'Pokaż pasek postępu',
        'points' => 'Punkty',
        'points_help' => 'Zarezerwowane — jeszcze niepowiązane z tabelą wyników.',

        'retention' => 'Trwałość',
        'retention_help' => 'Trwałe: zachowane na zawsze. Odwoływalne: utrzymane tylko, gdy warunek jest spełniony.',
        'is_active' => 'Aktywne',
    ],
];
