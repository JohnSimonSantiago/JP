<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lounge_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // null for walk-ins
            $table->enum('customer_type', ['member', 'walk_in'])->default('walk_in');
            $table->integer('user_level')->default(1); // snapshot of level at check-in
            $table->timestamp('checked_in_at');
            $table->timestamp('checked_out_at')->nullable();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->decimal('total_bill', 8, 2)->nullable(); // filled on checkout
            $table->boolean('is_free')->default(false); // true for level 2 and 3
            $table->foreignId('checked_in_by')->constrained('users')->onDelete('cascade'); // staff/admin who did check-in
            $table->timestamps();

            $table->index(['status', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lounge_sessions');
    }
};