<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lounge_sessions', function (Blueprint $table) {
            $table->json('notified_thresholds')->nullable()->after('billing_mode');
        });
    }

    public function down(): void
    {
        Schema::table('lounge_sessions', function (Blueprint $table) {
            $table->dropColumn('notified_thresholds');
        });
    }
};