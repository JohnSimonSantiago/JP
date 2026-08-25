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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            // Total pesos paid out to the shop in this claim
            $table->decimal('amount', 10, 2);
            // How many completed orders this payout covered (for the receipt/history)
            $table->unsignedInteger('order_count')->default(0);
            // Which admin marked it paid (nullable in case of system action)
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
