<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add is_approved column - new users will need approval to login
            $table->boolean('is_approved')->default(false)->after('is_premium');
            
            // Add index for better performance when checking approved users
            $table->index('is_approved');
        });

        // Approve all existing users so they can continue logging in
        // Remove this if you want to review existing users manually
        DB::table('users')->update(['is_approved' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_approved']);
            $table->dropColumn('is_approved');
        });
    }
};