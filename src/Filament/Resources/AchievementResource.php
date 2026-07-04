<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Resources;

use BackedEnum;
use Closure;
use Dainc007\Achievements\Domain\StatCatalog;
use Dainc007\Achievements\Enums\Retention;
use Dainc007\Achievements\Enums\Tier;
use Dainc007\Achievements\Filament\Resources\AchievementResource\Pages\CreateAchievement;
use Dainc007\Achievements\Filament\Resources\AchievementResource\Pages\EditAchievement;
use Dainc007\Achievements\Filament\Resources\AchievementResource\Pages\ListAchievements;
use Dainc007\Achievements\Filament\Support\BadgeIcon;
use Dainc007\Achievements\Models\Achievement;
use Dainc007\Achievements\Support\EvaluatorRegistry;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

final class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('achievements::achievements.nav_label');
    }

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('achievements::achievements.model_label');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('achievements::achievements.plural_label');
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        $statOptions = self::statOptions();

        return $schema->components([
            Section::make(__('achievements::achievements.form.section_definition'))
                ->icon('heroicon-o-identification')
                ->schema([
                    TextInput::make('key')
                        ->label(__('achievements::achievements.form.key'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('achievements::achievements.form.key_help')),
                    TextInput::make('name')
                        ->label(__('achievements::achievements.form.name'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('achievements::achievements.form.description'))
                        ->rows(2)
                        ->columnSpanFull(),
                    Select::make('type')
                        ->label(__('achievements::achievements.form.type'))
                        ->options(self::evaluatorOptions())
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText(__('achievements::achievements.form.type_help')),
                    TextInput::make('category')
                        ->label(__('achievements::achievements.form.category'))
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make(__('achievements::achievements.form.section_terms'))
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    // Until a config-bearing type is picked the section has no fields,
                    // so show a hint instead of an empty box.
                    Placeholder::make('terms_hint')
                        ->hiddenLabel()
                        ->content(__('achievements::achievements.form.terms_hint'))
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => $get('type') !== 'stat_threshold'),
                    // Config-driven "stat_threshold": pick a stat + a target, no raw keys.
                    ($statOptions === null
                        ? TextInput::make('config.stat')
                        : Select::make('config.stat')
                            ->options($statOptions)
                            ->searchable()
                            ->native(false)
                            ->placeholder(__('achievements::achievements.form.stat_placeholder'))
                    )
                        ->label(__('achievements::achievements.form.stat'))
                        ->helperText(__('achievements::achievements.form.stat_help'))
                        ->visible(fn (Get $get): bool => $get('type') === 'stat_threshold')
                        ->required(fn (Get $get): bool => $get('type') === 'stat_threshold'),
                    TextInput::make('config.target')
                        ->label(__('achievements::achievements.form.target'))
                        ->helperText(__('achievements::achievements.form.target_help'))
                        ->numeric()
                        ->minValue(1)
                        ->visible(fn (Get $get): bool => $get('type') === 'stat_threshold')
                        ->required(fn (Get $get): bool => $get('type') === 'stat_threshold'),
                ])
                ->columns(2),

            Section::make(__('achievements::achievements.form.section_presentation'))
                ->icon('heroicon-o-sparkles')
                ->schema([
                    TextInput::make('icon')
                        ->label(__('achievements::achievements.form.icon'))
                        ->maxLength(255)
                        ->helperText(__('achievements::achievements.form.icon_help'))
                        // Outer closure returns the Laravel rule so Filament does
                        // not try to dependency-inject the rule's ($attribute…) args.
                        ->rules([
                            fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                                if (is_string($value) && $value !== '' && ! BadgeIcon::exists($value)) {
                                    $fail(__('achievements::achievements.form.icon_invalid'));
                                }
                            },
                        ]),
                    FileUpload::make('image')
                        ->label(__('achievements::achievements.form.image'))
                        ->helperText(__('achievements::achievements.form.image_help'))
                        ->image()
                        ->disk(self::imageDisk())
                        ->visibility('public')
                        ->directory('achievement-badges'),
                    Select::make('tier')
                        ->label(__('achievements::achievements.form.tier'))
                        ->options(Tier::class),
                    Toggle::make('is_progressive')
                        ->label(__('achievements::achievements.form.is_progressive')),
                    TextInput::make('points')
                        ->label(__('achievements::achievements.form.points'))
                        ->numeric()
                        ->minValue(0)
                        ->helperText(__('achievements::achievements.form.points_help')),
                ])
                ->columns(2),

            Section::make(__('achievements::achievements.form.section_behaviour'))
                ->icon('heroicon-o-cog-6-tooth')
                ->schema([
                    Select::make('retention')
                        ->label(__('achievements::achievements.form.retention'))
                        ->options(Retention::class)
                        ->default(Retention::Permanent->value)
                        ->required()
                        ->helperText(__('achievements::achievements.form.retention_help')),
                    Toggle::make('is_active')
                        ->label(__('achievements::achievements.form.is_active'))
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('icon')
                    ->label('')
                    ->icon(fn (Achievement $record): string => BadgeIcon::resolve($record->icon))
                    ->color(fn (Achievement $record): string|array => self::tierColor($record->tier)),
                TextColumn::make('name')
                    ->label(__('achievements::achievements.table.name'))
                    ->description(fn (Achievement $record): string => $record->key)
                    ->searchable(['name', 'key'])
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('achievements::achievements.table.type'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => self::typeLabel($state)),
                TextColumn::make('tier')
                    ->label(__('achievements::achievements.table.tier'))
                    ->badge()
                    ->color(fn (?Tier $state): string|array => self::tierColor($state))
                    ->formatStateUsing(fn (?Tier $state): string => self::tierLabel($state))
                    ->placeholder('—'),
                TextColumn::make('retention')
                    ->label(__('achievements::achievements.table.retention'))
                    ->badge()
                    ->color(fn (?Retention $state): string => $state === Retention::Revocable ? 'warning' : 'success')
                    ->formatStateUsing(fn (?Retention $state): string => $state instanceof Retention
                        ? __('achievements::achievements.retentions.'.$state->value)
                        : '—'),
                IconColumn::make('is_progressive')
                    ->label(__('achievements::achievements.table.is_progressive'))
                    ->boolean(),
                ToggleColumn::make('is_active')
                    ->label(__('achievements::achievements.table.is_active')),
            ])
            ->filters([
                SelectFilter::make('tier')->options(Tier::class),
                SelectFilter::make('retention')->options(Retention::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListAchievements::route('/'),
            'create' => CreateAchievement::route('/create'),
            'edit' => EditAchievement::route('/{record}/edit'),
        ];
    }

    /**
     * Evaluator type options for the "type" picker, labelled from the registered
     * evaluator keys. Falls back to the built-in "stat_threshold" when none are
     * registered yet (e.g. before the consuming app wires its evaluators).
     *
     * @return array<string, string>
     */
    private static function evaluatorOptions(): array
    {
        $keys = app(EvaluatorRegistry::class)->keys();

        if ($keys === []) {
            $keys = ['stat_threshold'];
        }

        $options = [];

        foreach ($keys as $key) {
            $options[$key] = Str::headline($key);
        }

        return $options;
    }

    /**
     * Stat options from the app-provided catalog, or null when none is bound
     * (the stat field then falls back to a free-text input).
     *
     * @return array<string, string>|null
     */
    private static function statOptions(): ?array
    {
        if (! app()->bound(StatCatalog::class)) {
            return null;
        }

        $options = app(StatCatalog::class)->options();

        return $options === [] ? null : $options;
    }

    private static function imageDisk(): string
    {
        $disk = config('achievements.image_disk');

        return is_string($disk) && $disk !== '' ? $disk : 'public';
    }

    /**
     * Tier → table badge/icon colour. Hues chosen to read distinctly from one
     * another in the dark app panel (orange/zinc/amber/fuchsia).
     *
     * @return string|array<int, string>
     */
    private static function tierColor(?Tier $tier): string|array
    {
        return match ($tier) {
            Tier::Bronze => Color::Orange,
            Tier::Silver => Color::Zinc,
            Tier::Gold => Color::Amber,
            Tier::Legendary => Color::Fuchsia,
            default => 'gray',
        };
    }

    /**
     * Tier → translated label, falling back to a dash when unset.
     */
    private static function tierLabel(?Tier $tier): string
    {
        return $tier instanceof Tier
            ? __('achievements::achievements.tiers.'.$tier->value)
            : '—';
    }

    /**
     * Evaluator type → translated label, humanising the key when no
     * translation exists (so app-registered custom types still read cleanly).
     */
    private static function typeLabel(string $type): string
    {
        $key = 'achievements::achievements.types.'.$type;
        $translated = __($key);

        return $translated === $key ? Str::headline($type) : $translated;
    }
}
