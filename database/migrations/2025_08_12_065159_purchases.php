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
          Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_item_id')->constrained()->onDelete('cascade');
            $table->integer('price_paid'); // price at time of purchase
            $table->integer('quantity')->default(1);
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable(); // reason for rejection
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('shop_item_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::dropIfExists('purchases');
    }
};
