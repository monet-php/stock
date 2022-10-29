<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_stock', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->index()->constrained()->cascadeOnDelete();
            $table->decimal('amount');
        });
    }
};
