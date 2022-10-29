<?php

namespace Monet\Stock\Filament\Resources\ProductResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Monet\Stock\Filament\Resources\ProductResource;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;
}