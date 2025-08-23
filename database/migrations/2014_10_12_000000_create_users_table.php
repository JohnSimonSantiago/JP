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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique()->nullable(); // Optional email for auth
            $table->timestamp('email_verified_at')->nullable();
            $table->text('bio')->nullable();
            $table->string('role')->default('user'); // ADDED: For admin functionality
            $table->integer('level')->default(1);
            $table->integer('points')->default(100); // FIXED: Consistent with stars
            $table->integer('stars')->default(100);
            $table->decimal('cash', 10, 2)->default(0.00);
            $table->boolean('is_premium')->default(false);
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->rememberToken(); // For Laravel auth
            $table->timestamps();

            // Add indexes for better performance
            $table->index('role');
            $table->index('stars');
            $table->index('points');
            $table->index('level');
            $table->index('is_premium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};