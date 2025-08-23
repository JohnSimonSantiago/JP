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
        Schema::table('users', function (Blueprint $table) {
            // Add new profile fields
            $table->string('gender')->nullable()->after('profile_image');
            $table->date('birthday')->nullable()->after('gender');
            $table->text('address')->nullable()->after('birthday');
            $table->json('privacy_settings')->nullable()->after('address');
            
            // Add indexes for better performance
            $table->index('gender');
            $table->index('birthday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['gender']);
            $table->dropIndex(['birthday']);
            
            // Drop columns
            $table->dropColumn([
                'gender', 
                'birthday', 
                'address', 
                'privacy_settings'
            ]);
        });
    }
};