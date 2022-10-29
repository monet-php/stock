<?php

namespace Monet\Stock\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Monet\Stock\Enums\ProductUnit;

class StockRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';

    protected static ?string $recordTitleAttribute = 'date';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }
}
