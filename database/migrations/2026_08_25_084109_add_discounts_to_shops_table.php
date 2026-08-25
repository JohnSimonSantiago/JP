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
        Schema::table('shops', function (Blueprint $table) {
            // Discount we (Level Lounge) shoulder, in whole percent (0-100)
            $table->unsignedTinyInteger('admin_discount_percent')->default(0)->after('is_verified');
            // Discount the shop shoulders, in whole percent (0-100)
            $table->unsignedTinyInteger('store_discount_percent')->default(0)->after('admin_discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['admin_discount_percent', 'store_discount_percent']);
        });
    }
};
