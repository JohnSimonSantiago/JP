<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lounge_pricing', function (Blueprint $table) {
            $table->id();
            $table->decimal('hourly_rate', 8, 2)->default(40.00);
            $table->decimal('bundle_rate', 8, 2)->default(100.00); // per 3 hours
            $table->integer('bundle_hours')->default(3);
            $table->decimal('day_rate', 8, 2)->default(200.00);
            $table->timestamps();
        });

        // Seed default pricing
        DB::table('lounge_pricing')->insert([
            'hourly_rate' => 40.00,
            'bundle_rate' => 100.00,
            'bundle_hours' => 3,
            'day_rate' => 200.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('lounge_pricing');
    }
};