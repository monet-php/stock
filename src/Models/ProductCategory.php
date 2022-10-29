<?php

namespace Monet\Stock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
