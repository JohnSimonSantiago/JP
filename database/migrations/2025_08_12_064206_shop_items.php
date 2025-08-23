<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price')->default(0); // points required to buy
            $table->decimal('cash_price', 10, 2)->default(0.00); // cash price
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_active_in_point_shop')->default(true);
            $table->integer('stock')->nullable(); // null = unlimited stock
            $table->timestamps();
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_items');
    }
};