<?php

namespace Monet\Stock\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductStock extends Pivot
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'amount',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'amount' => 'float'
    ];
}
