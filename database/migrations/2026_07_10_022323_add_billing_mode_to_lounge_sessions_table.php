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
    Schema::table('lounge_sessions', function (Blueprint $table) {
        $table->enum('billing_mode', ['hourly', 'consumable'])->default('hourly')->after('is_free');
    });
}

public function down(): void
{
    Schema::table('lounge_sessions', function (Blueprint $table) {
        $table->dropColumn('billing_mode');
    });
}
};
