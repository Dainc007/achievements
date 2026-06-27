<?php

declare(strict_types=1);

namespace Dainc007\Achievements\Filament\Resources;

use BackedEnum;
use Dainc007\Achievements\Enums\Retention;
use Dainc007\Achievements\Enums\Tier;
use Dainc007\Achievements\Filament\Resources\AchievementResource\Pages\CreateAchievement;
use Dainc007\Achievements\Filament\Resources\AchievementResource\Pages\EditAchievement;
use Dainc007\Achievements\Filament\Resources\AchievementResource\Pages\ListAchievements;
use Dainc007\Achievements\Models\Achievement;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Definition')
                ->schema([
                    TextInput::make('key')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Stable identifier, e.g. "goal_machine". Cannot collide.'),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make('type')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Evaluator key registered in the app, e.g. "stat_threshold".'),
                    TextInput::make('category')
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('Configuration')
                ->schema([
                    KeyValue::make('config')
                        ->keyLabel('Setting')
                        ->valueLabel('Value')
                        ->helperText('Evaluator config, e.g. stat / target.')
                        ->columnSpanFull(),
                ]),

            Section::make('Presentation')
                ->schema([
                    TextInput::make('icon')
                        ->maxLength(255)
                        ->helperText('Heroicon name, e.g. "heroicon-o-trophy".'),
                    Select::make('tier')
                        ->options(Tier::class),
                    Toggle::make('is_progressive')
                        ->helperText('Show a progress bar toward the target.'),
                    TextInput::make('points')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('Reserved — not yet wired to a leaderboard.'),
                ])
                ->columns(2),

            Section::make('Behaviour')
                ->schema([
                    Select::make('retention')
                        ->options(Retention::class)
                        ->default(Retention::Permanent->value)
                        ->required()
                        ->helperText('Permanent: kept for life. Revocable: held only while true.'),
                    Toggle::make('is_active')
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
}
