<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lounge_sessions', function (Blueprint $table) {
            $table->string('school')->nullable()->after('customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('lounge_sessions', function (Blueprint $table) {
            $table->dropColumn('school');
        });
    }
};