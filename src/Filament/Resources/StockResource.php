<?php

namespace Monet\Stock\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Monet\Stock\Filament\Resources\StockResource\Pages\CreateStock;
use Monet\Stock\Filament\Resources\StockResource\Pages\EditStock;
use Monet\Stock\Filament\Resources\StockResource\Pages\ListStocks;
use Monet\Stock\Filament\Resources\StockResource\RelationManagers\ProductRelationManager;
use Monet\Stock\Models\Stock;
use Str;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $slug = 'stock/stocks';

    protected static ?string $navigationGroup = 'Stock';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

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
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()->startOfWeek()),
                        Forms\Components\Select::make('category_id')
                            ->required()
                            ->relationship('category', 'name')
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                            ]),
                        Forms\Components\MarkdownEditor::make('notes')
                            ->maxLength(65_535)
                            ->columnSpan(2)
                    ]),

                Forms\Components\Card::make()
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(?Stock $record): string => $record?->created_at?->diffForHumans() ?? '-'),
                        Forms\Components\Placeholder::make('update_at')
                            ->label('Updated at')
                            ->content(fn(?Stock $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->getStateUsing(
                        fn(Stock $record): string => $record->notes === null
                            ? '-'
                            : Str::of($record->notes ?? '-')->markdown()->toHtmlString()
                    )
                    ->html(),
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
            'index' => ListStocks::route('/'),
            'create' => CreateStock::route('/create'),
            'edit' => EditStock::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ProductRelationManager::class
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
