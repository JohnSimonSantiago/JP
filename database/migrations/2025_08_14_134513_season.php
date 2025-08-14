<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create seasons table
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->integer('season_number'); // Season 1, 2, 3, etc.
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null for current season
            $table->boolean('is_current')->default(false);
            $table->json('top_players')->nullable(); // Store top 3 players data
            $table->timestamps();
        });

        // Create season_rankings table for historical records
        Schema::create('season_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rank'); // 1, 2, 3
            $table->integer('final_stars'); // Stars at end of season
            $table->timestamps();
            
            $table->unique(['season_id', 'rank']); // Only one rank 1, 2, 3 per season
        });
    }

    public function down()
    {
        Schema::dropIfExists('season_rankings');
        Schema::dropIfExists('seasons');
    }
};