<?php

namespace Monet\Stock\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Monet\Stock\Enums\ProductUnit;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'category_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'unit' => ProductUnit::class
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class)
            ->using(ProductStock::class)
            ->withPivot('amount');
    }

    public function stock(): Attribute
    {
        return Attribute::make(
            get: fn(): ?Stock => $this->stocks?->last()
        );
    }

    public function amount(): Attribute
    {
        return Attribute::make(
            get: fn(): ?float => $this->stock?->pivot?->amount
        );
    }

    public function amountWithUnit(): Attribute
    {
        return Attribute::make(
            get: function(): ?string {
                $amount = $this->amount;
                if($amount === null) {
                    return null;
                }

                return $amount . $this->unit->value;
            }
        );
    }
}
