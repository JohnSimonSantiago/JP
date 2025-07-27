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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id('reservationNumber');
            $table->string('customerName');
            $table->string('address');
            $table->integer('roomNumber');
            $table->integer('reservationType');
            $table->integer('roomType');
            $table->integer('additionalHours')->nullable();
            $table->tinyInteger('statusID')->default(1); 
            $table->timestamp('timeOut')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
