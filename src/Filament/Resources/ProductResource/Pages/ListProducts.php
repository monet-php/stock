<?php

namespace Monet\Stock\Filament\Resources\ProductResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Monet\Stock\Filament\Resources\ProductResource;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
}
