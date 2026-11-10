<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lounge_sessions', function (Blueprint $table) {
            // Set when a member times out with a negative consumable balance ("Needs payment" list).
            // Cleared when they buy enough time (balance back to 0+) or staff press "Pay next visit".
            $table->timestamp('payment_pending_at')->nullable()->after('checked_out_at');
            $table->index('payment_pending_at');
        });
    }

    public function down(): void
    {
        Schema::table('lounge_sessions', function (Blueprint $table) {
            $table->dropIndex(['payment_pending_at']);
            $table->dropColumn('payment_pending_at');
        });
    }
};
