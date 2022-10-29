<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', static function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
