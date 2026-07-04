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
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
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
    public static function form(Schema $schema): Schema
    {
        $statOptions = self::statOptions();

        return $schema->components([
            Section::make(__('achievements::achievements.form.section_definition'))
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
                ->schema([
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
                    // Other evaluator types keep a free-form config editor.
                    KeyValue::make('config')
                        ->label(__('achievements::achievements.form.config'))
                        ->helperText(__('achievements::achievements.form.config_help'))
                        ->visible(fn (Get $get): bool => ! in_array($get('type'), [null, '', 'stat_threshold'], true))
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make(__('achievements::achievements.form.section_presentation'))
                ->schema([
                    TextInput::make('icon')
                        ->label(__('achievements::achievements.form.icon'))
                        ->maxLength(255)
                        ->helperText(__('achievements::achievements.form.icon_help'))
                        ->rules([
                            static function (string $attribute, mixed $value, Closure $fail): void {
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
                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('tier')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('retention')
                    ->badge(),
                IconColumn::make('is_progressive')
                    ->boolean(),
                ToggleColumn::make('is_active'),
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
}
