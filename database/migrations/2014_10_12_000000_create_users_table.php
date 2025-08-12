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
$table->integer('level')->default(1); // users start at level 1
$table->integer('points')->default(0); // users start with 0 points
$table->boolean('is_premium')->default(false); // indicates membership status
$table->string('password');
$table->string('profile_image')->nullable(); 
$table->timestamps();
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
