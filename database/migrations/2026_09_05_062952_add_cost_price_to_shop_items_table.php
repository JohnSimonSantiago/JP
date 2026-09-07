<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_items', function (Blueprint $table) {
            // What one unit costs the shop — used for profit reports. Owner's estimate.
            $table->decimal('cost_price', 10, 2)->default(0)->after('cash_price');
        });
    }

    public function down(): void
    {
        Schema::table('shop_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};