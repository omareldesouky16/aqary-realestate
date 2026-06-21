<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'My Properties';

    protected static ?string $navigationGroup = 'Property Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Egyptian city to region mapping.
     * Used for hierarchical dependent dropdowns in the form.
     */
    protected static array $egyptianLocations = [
        'Cairo' => [
            'New Cairo'         => 'New Cairo',
            'Maadi'             => 'Maadi',
            'Heliopolis'        => 'Heliopolis',
            'Nasr City'         => 'Nasr City',
            'Zamalek'           => 'Zamalek',
            'Downtown Cairo'    => 'Downtown Cairo',
            'Ain Shams'         => 'Ain Shams',
            'Shubra'            => 'Shubra',
            'Mostorod'          => 'Mostorod',
            'Badr City'         => 'Badr City',
            'El Shorouk'        => 'El Shorouk',
            'New Administrative Capital' => 'New Administrative Capital',
        ],
        'Giza' => [
            'Sheikh Zayed'  => 'Sheikh Zayed',
            '6th of October'=> '6th of October',
            'Dokki'         => 'Dokki',
            'Mohandessin'   => 'Mohandessin',
            'Haram'         => 'Haram',
            'Faisal'        => 'Faisal',
            'Imbaba'        => 'Imbaba',
            'Agouza'        => 'Agouza',
            'Smart Village Area' => 'Smart Village Area',
        ],
        'Alexandria' => [
            'Smouha'        => 'Smouha',
            'Gleem'         => 'Gleem',
            'San Stefano'   => 'San Stefano',
            'Sidi Gaber'    => 'Sidi Gaber',
            'Roushdy'       => 'Roushdy',
            'Miami'         => 'Miami',
            'Montazah'      => 'Montazah',
            'Borg El Arab'  => 'Borg El Arab',
            'New Alexandria' => 'New Alexandria',
        ],
        'North Coast' => [
            'Sahel'         => 'Sahel',
            'Marina'        => 'Marina',
            'Hacienda Bay'  => 'Hacienda Bay',
            'Marassi'       => 'Marassi',
            'Sidi Abdel Rahman' => 'Sidi Abdel Rahman',
            'Alamein'       => 'Alamein',
        ],
        'Red Sea' => [
            'Hurghada'      => 'Hurghada',
            'El Gouna'      => 'El Gouna',
            'Sahl Hasheesh' => 'Sahl Hasheesh',
            'Soma Bay'      => 'Soma Bay',
            'Makadi Bay'    => 'Makadi Bay',
        ],
        'South Sinai' => [
            'Sharm El Sheikh' => 'Sharm El Sheikh',
            'Dahab'           => 'Dahab',
            'Nuweiba'         => 'Nuweiba',
            'Taba'            => 'Taba',
        ],
        'Suez Canal' => [
            'Ismailia'      => 'Ismailia',
            'Port Said'     => 'Port Said',
            'Suez City'     => 'Suez City',
        ],
        'Delta Region' => [
            'Mansoura'      => 'Mansoura',
            'Tanta'         => 'Tanta',
            'Zagazig'       => 'Zagazig',
            'Damanhour'     => 'Damanhour',
            'Kafr El Sheikh'=> 'Kafr El Sheikh',
        ],
    ];

    // -------------------------------------------------------------------------
    // FORM
    // -------------------------------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([

                // ── STEP 1: BASIC INFORMATION ────────────────────────────────
                Step::make('Basic Information')
                    ->icon('heroicon-o-information-circle')
                    ->description('Enter the core property details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Property Title')
                                ->placeholder('e.g. Luxury Villa in New Cairo with Private Pool')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Select::make('property_type')
                                ->label('Property Type')
                                ->options([
                                    'apartment' => '🏢 Apartment',
                                    'house'     => '🏠 House',
                                    'villa'     => '🏡 Villa',
                                    'studio'    => '🛋️ Studio',
                                ])
                                ->required()
                                ->native(false)
                                ->placeholder('Select type…'),

                            Select::make('payment_type')
                                ->label('Payment Type')
                                ->options([
                                    'cash'         => '💵 Cash',
                                    'installments' => '📆 Installments',
                                ])
                                ->required()
                                ->native(false)
                                ->placeholder('Select payment method…'),

                            TextInput::make('price')
                                ->label('Price (EGP)')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->prefix('EGP')
                                ->placeholder('0.00'),

                            TextInput::make('area_sqm')
                                ->label('Area (sqm)')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->suffix('m²')
                                ->placeholder('0'),

                            Toggle::make('is_furnished')
                                ->label('Furnished')
                                ->helperText('Toggle on if the property is fully or partially furnished')
                                ->default(false),
                                
                            Hidden::make('seller_id')
                                ->default(fn () => auth()->id()),
                        ]),
                    ]),

                // ── STEP 2: ROOMS & LOCATION ─────────────────────────────────
                Step::make('Rooms & Location')
                    ->icon('heroicon-o-map-pin')
                    ->description('Specify room counts and geographic location')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('bedrooms')
                                ->label('Bedrooms')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(30)
                                ->step(1)
                                ->placeholder('0'),

                            TextInput::make('bathrooms')
                                ->label('Bathrooms')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(20)
                                ->step(1)
                                ->placeholder('0'),

                            Select::make('city')
                                ->label('City')
                                ->options(array_combine(
                                    array_keys(static::$egyptianLocations),
                                    array_keys(static::$egyptianLocations)
                                ))
                                ->required()
                                ->native(false)
                                ->placeholder('Select city…')
                                ->live()
                                ->afterStateUpdated(fn ($set) => $set('region', null)),

                            Select::make('region')
                                ->label('Region / District')
                                ->options(function (Get $get): array {
                                    $city = $get('city');
                                    if (! $city || ! isset(static::$egyptianLocations[$city])) {
                                        return [];
                                    }
                                    return static::$egyptianLocations[$city];
                                })
                                ->required()
                                ->native(false)
                                ->placeholder('Select region…')
                                ->disabled(fn (Get $get): bool => ! $get('city'))
                                ->helperText('Select a city first to load its regions'),
                        ]),
                    ]),

                // ── STEP 3: FEATURES & AVAILABILITY ─────────────────────────
                Step::make('Features & Availability')
                    ->icon('heroicon-o-star')
                    ->description('List amenities and available inspection timeslots')
                    ->schema([
                        Grid::make(1)->schema([
                            TagsInput::make('features')
                                ->label('Property Features / Amenities')
                                ->placeholder('Add a feature and press Enter…')
                                ->suggestions([
                                    'Swimming Pool',
                                    'Private Garden',
                                    'Covered Parking',
                                    'Security 24/7',
                                    'Gym',
                                    'Elevator',
                                    'Balcony',
                                    'Central AC',
                                    'Rooftop Terrace',
                                    'Storage Room',
                                    'Maid\'s Room',
                                    'Smart Home System',
                                    'Solar Panels',
                                    'Private Pool',
                                    'Clubhouse Access',
                                    'Backup Generator',
                                    'Water Tank',
                                    'Intercom System',
                                    'CCTV',
                                    'Pet Friendly',
                                ])
                                ->helperText('Type each amenity and press Enter to add it.'),

                            Repeater::make('timeslots')
                                ->label('Available Inspection Timeslots')
                                ->simple(
                                    DateTimePicker::make('time')
                                        ->native(false)
                                        ->displayFormat('l, M j, Y h:i A')
                                        ->minDate(now())
                                        ->required()
                                )
                                ->addActionLabel('Add Timeslot')
                                ->reorderable(false)
                                ->helperText('Add exact date and time windows available for property inspections.'),
                        ]),
                    ]),

                // ── STEP 4: PHOTOS ───────────────────────────────────────────
                Step::make('Photos')
                    ->icon('heroicon-o-photo')
                    ->description('Upload high-quality property photos (max 15)')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Property Images')
                            ->multiple()
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->maxFiles(15)
                            ->maxSize(8192) // 8 MB per file
                            ->disk('public')
                            ->directory('properties/images')
                            ->reorderable()
                            ->panelLayout('grid')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->uploadingMessage('Uploading images…')
                            ->helperText('Upload up to 15 images. Accepted formats: JPEG, PNG, WebP. Max 8MB per file.')
                            ->columnSpanFull(),
                    ]),

            ])
            ->skippable(false)
            ->columnSpanFull(),
        ]);
    }

    // -------------------------------------------------------------------------
    // TABLE
    // -------------------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Property')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->title),

                TextColumn::make('property_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'villa'     => 'success',
                        'house'     => 'info',
                        'apartment' => 'warning',
                        'studio'    => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('region')
                    ->label('Region')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label('Price (EGP)')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ',')
                    ->prefix('EGP ')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('area_sqm')
                    ->label('Area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('payment_type')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash'         => 'success',
                        'installments' => 'info',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                IconColumn::make('is_furnished')
                    ->label('Furnished')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('favorites_count')
                    ->label('Favorites')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-m-heart')
                    ->color('danger')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Listed')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('property_type')
                    ->label('Property Type')
                    ->options([
                        'apartment' => 'Apartment',
                        'house'     => 'House',
                        'villa'     => 'Villa',
                        'studio'    => 'Studio',
                    ]),

                SelectFilter::make('payment_type')
                    ->label('Payment Type')
                    ->options([
                        'cash'         => 'Cash',
                        'installments' => 'Installments',
                    ]),

                SelectFilter::make('city')
                    ->label('City')
                    ->options(array_combine(
                        array_keys(static::$egyptianLocations),
                        array_keys(static::$egyptianLocations)
                    )),

                Tables\Filters\TernaryFilter::make('is_furnished')
                    ->label('Furnished'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateHeading('No Properties Yet')
            ->emptyStateDescription('Start listing your first property by clicking the button below.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    // -------------------------------------------------------------------------
    // PAGES
    // -------------------------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit'   => Pages\EditProperty::route('/{record}/edit'),
        ];
    }

    // -------------------------------------------------------------------------
    // QUERY SCOPING (Sellers only see their own listings)
    // -------------------------------------------------------------------------

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('seller_id', Auth::id());
    }
}
