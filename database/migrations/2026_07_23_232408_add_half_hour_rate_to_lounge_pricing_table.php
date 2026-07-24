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
        Schema::table('lounge_pricing', function (Blueprint $table) {
            $table->decimal('half_hour_rate', 8, 2)->default(25)->after('hourly_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lounge_pricing', function (Blueprint $table) {
            $table->dropColumn('half_hour_rate');
        });
    }
};
