<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            // What was actually paid, frozen when approved (gifted = 0). Null = nothing
            // paid yet (pending / rejected). Old rows: php artisan memberships:backfill-amounts
            $table->decimal('amount_paid', 10, 2)->nullable()->after('source');
            $table->timestamp('approved_at')->nullable()->after('amount_paid'); // revenue counts on this day
            $table->index('approved_at');
        });

        Schema::table('lounge_pricing', function (Blueprint $table) {
            // Standard membership prices: only the DEFAULT for "Amount received" when approving
            $table->decimal('membership_level2_price', 8, 2)->default(2500)->after('day_rate');
            $table->decimal('membership_level3_price', 8, 2)->default(3000)->after('membership_level2_price');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropIndex(['approved_at']);
            $table->dropColumn(['amount_paid', 'approved_at']);
        });
        Schema::table('lounge_pricing', function (Blueprint $table) {
            $table->dropColumn(['membership_level2_price', 'membership_level3_price']);
        });
    }
};
