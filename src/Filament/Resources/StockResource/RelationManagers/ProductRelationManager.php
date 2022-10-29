<?php

namespace Monet\Stock\Filament\Resources\StockResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Monet\Stock\Enums\ProductUnit;
use Monet\Stock\Models\Product;

class ProductRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                Forms\Components\Grid::make()
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
                            ->relationship('category', 'name')
                            ->default(function (ProductRelationManager $livewire) {
                                return $livewire->ownerRecord->category->id;
                            })
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                    ])
                    ->columns([
                        'sm' => 2,
                    ])
                    ->columnSpan([
                        'sm' => 2,
                    ])
            );
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
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn(Product $record, float $state): string => $state . $record->unit->value),
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
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                    ]),
                Tables\Actions\CreateAction::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
                Tables\Actions\DeleteBulkAction::make()
            ]);
    }
}
