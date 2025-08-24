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
        Schema::table('purchases', function (Blueprint $table) {
            // Change price_paid to decimal to handle both points (integers) and cash (decimals)
            $table->decimal('price_paid', 10, 2)->change();
            
            // Add currency type to track what was used for payment
            $table->enum('currency_type', ['points', 'cash'])->default('points')->after('price_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->integer('price_paid')->change();
            $table->dropColumn('currency_type');
        });
    }
};