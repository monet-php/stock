<?php

namespace Monet\Stock\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Monet\Stock\Enums\ProductUnit;
use Monet\Stock\Filament\Resources\ProductResource\Pages\CreateProduct;
use Monet\Stock\Filament\Resources\ProductResource\Pages\EditProduct;
use Monet\Stock\Filament\Resources\ProductResource\Pages\ListProducts;
use Monet\Stock\Filament\Resources\ProductResource\RelationManagers\StockRelationManager;
use Monet\Stock\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $slug = 'stock/products';

    protected static ?string $navigationGroup = 'Stock';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->columns([
                'sm' => 3,
                'lg' => null,
            ])
            ->schema([
                Forms\Components\Card::make()
                    ->columns([
                        'sm' => 2,
                    ])
                    ->columnSpan([
                        'sm' => 2,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('unit')
                            ->required()
                            ->options(
                                collect(ProductUnit::cases())->pluck('name', 'value')
                            ),
                        Forms\Components\Select::make('category_id')
                            ->required()
                            ->relationship('category', 'name')
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                            ])
                    ]),

                Forms\Components\Card::make()
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(?Product $record): string => $record?->created_at?->diffForHumans() ?? '-'),
                        Forms\Components\Placeholder::make('update_at')
                            ->label('Updated at')
                            ->content(fn(?Product $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn(Product $record): string => $record->unit->name),
                Tables\Columns\TextColumn::make('amount_with_unit')
                    ->label('Amount')
                    ->formatStateUsing(fn($state): string => $state ?? '-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->date()
                    ->sortable()
                    ->searchable()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            StockRelationManager::class
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }
}
