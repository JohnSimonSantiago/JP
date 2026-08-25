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
            // The shop owner's original list price at time of sale (per unit)
            $table->decimal('list_price', 10, 2)->nullable()->after('price_paid');
            // Admin discount slice in whole pesos (we shoulder this)
            $table->unsignedInteger('admin_discount_amount')->default(0)->after('list_price');
            // Store discount slice in whole pesos (shop shoulders this)
            $table->unsignedInteger('store_discount_amount')->default(0)->after('admin_discount_amount');
            // What the shop can claim from us for this purchase (per unit): list_price - store_discount_amount
            $table->decimal('shop_claim_amount', 10, 2)->default(0)->after('store_discount_amount');
            // Payout tracking: has this completed order been paid out to the shop yet?
            $table->enum('payout_status', ['unremitted', 'remitted'])->default('unremitted')->after('shop_claim_amount');
            // Which payout batch this was included in (filled later when Step 4 exists)
            $table->unsignedBigInteger('payout_id')->nullable()->after('payout_status');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'list_price',
                'admin_discount_amount',
                'store_discount_amount',
                'shop_claim_amount',
                'payout_status',
                'payout_id',
            ]);
        });
    }
};
