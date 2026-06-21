<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Filament\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Basic Info')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Select::make('property_type')
                                ->options([
                                    'apartment' => 'Apartment',
                                    'house' => 'House',
                                    'villa' => 'Villa',
                                    'studio' => 'Studio',
                                ])
                                ->required(),
                            Forms\Components\Hidden::make('seller_id')
                                ->default(fn () => auth()->id()),
                        ]),
                    Forms\Components\Wizard\Step::make('Location')
                        ->schema([
                            Forms\Components\Select::make('city')
                                ->options([
                                    'Cairo' => 'Cairo',
                                    'Giza' => 'Giza',
                                    'Alexandria' => 'Alexandria',
                                ])
                                ->live()
                                ->required(),
                            Forms\Components\Select::make('region')
                                ->options(fn (Forms\Get $get): array => match ($get('city')) {
                                    'Cairo' => [
                                        'New Cairo' => 'New Cairo',
                                        'Nasr City' => 'Nasr City',
                                        'Maadi' => 'Maadi',
                                    ],
                                    'Giza' => [
                                        'Sheikh Zayed' => 'Sheikh Zayed',
                                        '6th of October' => '6th of October',
                                        'Dokki' => 'Dokki',
                                    ],
                                    'Alexandria' => [
                                        'Smouha' => 'Smouha',
                                        'Sidi Beshr' => 'Sidi Beshr',
                                    ],
                                    default => [],
                                })
                                ->required(),
                        ]),
                    Forms\Components\Wizard\Step::make('Pricing & Structure')
                        ->schema([
                            Forms\Components\Select::make('payment_type')
                                ->options([
                                    'cash' => 'Cash',
                                    'installments' => 'Installments',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('EGP'),
                            Forms\Components\TextInput::make('area_sqm')
                                ->required()
                                ->numeric()
                                ->label('Area (Sqm)'),
                            Forms\Components\TextInput::make('bedrooms')
                                ->required()
                                ->numeric(),
                            Forms\Components\TextInput::make('bathrooms')
                                ->required()
                                ->numeric(),
                            Forms\Components\Toggle::make('is_furnished')
                                ->required(),
                        ]),
                    Forms\Components\Wizard\Step::make('Features & Media')
                        ->schema([
                            Forms\Components\TagsInput::make('features')
                                ->placeholder('e.g. Pool, Gym, Security'),
                            Forms\Components\Repeater::make('timeslots')
                                ->simple(
                                    Forms\Components\DateTimePicker::make('time')
                                        ->native(false)
                                        ->displayFormat('l, M j, Y h:i A')
                                        ->minDate(now())
                                        ->required()
                                )
                                ->addActionLabel('Add Timeslot')
                                ->reorderable(false),
                            Forms\Components\FileUpload::make('images')
                                ->multiple()
                                ->maxFiles(15)
                                ->directory('property-images'),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('property_type'),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('area_sqm')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_type'),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_furnished')
                    ->boolean(),
                Tables\Columns\TextColumn::make('views_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('favorites_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('seller.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
