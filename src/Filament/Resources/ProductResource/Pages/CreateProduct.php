<?php

namespace Monet\Stock\Filament\Resources\ProductResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Monet\Stock\Filament\Resources\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}