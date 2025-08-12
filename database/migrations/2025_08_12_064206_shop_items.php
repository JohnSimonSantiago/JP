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
            $table->integer('price'); // points required to buy
            $table->string('image')->nullable();
            $table->enum('category', ['cosmetic', 'boost', 'premium', 'special'])->default('cosmetic');
            $table->boolean('is_active')->default(true);
            $table->integer('stock')->nullable(); // null = unlimited stock
            $table->json('properties')->nullable(); // for special item properties (e.g., point boost amount)
            $table->timestamps();

            $table->index(['category', 'is_active']);
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
