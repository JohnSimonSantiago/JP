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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->integer('season_number')->unique(); // Season 1, 2, 3, etc.
            $table->timestamp('start_date'); // FIXED: Use timestamp instead of date
            $table->timestamp('end_date')->nullable(); // null for current season
            $table->boolean('is_current')->default(false);
            $table->json('top_players')->nullable(); // Store top 3 players data as JSON
            $table->timestamps();

            // Indexes for better performance
            $table->index('is_current');
            $table->index('season_number');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};