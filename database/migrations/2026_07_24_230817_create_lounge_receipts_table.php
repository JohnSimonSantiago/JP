<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lounge_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->integer('number');
            $table->string('group_id')->nullable();
            $table->timestamps();

            // Two check-ins can never grab the same monthly CN number
            $table->unique(['year', 'month', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lounge_receipts');
    }
};