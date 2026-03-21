<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Make user_id optional (walk-ins have no account)
            $table->foreignId('user_id')->nullable()->change();

            // Who placed the order: registered user or walk-in
            $table->enum('customer_type', ['user', 'walk_in'])->default('user')->after('user_id');

            // Walk-in customer name (e.g. "Juan" or "Table 3")
            $table->string('walk_in_name')->nullable()->after('customer_type');

            // How they paid: digital balance or physical cash
            $table->enum('payment_method', ['balance', 'cash'])->default('balance')->after('walk_in_name');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn(['customer_type', 'walk_in_name', 'payment_method']);
        });
    }
};