<?php

namespace Monet\Stock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'notes',
        'category_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->using(ProductStock::class)
            ->withPivot('amount');
    }
}
