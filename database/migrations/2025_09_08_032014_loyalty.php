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
        // Create loyalty_cards table - One per shop
        Schema::create('loyalty_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('Loyalty Card'); // Card name/title
            $table->text('description')->nullable(); // Card description
            $table->integer('required_purchases')->default(10); // Items needed for free item
            $table->boolean('is_active')->default(true); // Can be disabled by shop owner
            $table->timestamps();
            
            // Ensure one loyalty card per shop
            $table->unique('shop_id');
            $table->index(['shop_id', 'is_active']);
        });

        // Create loyalty_card_progress table - Track customer progress
        Schema::create('loyalty_card_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('current_purchases')->default(0); // Current purchase count
            $table->integer('completed_cards')->default(0); // How many full cards completed
            $table->timestamp('last_purchase_at')->nullable(); // Track last activity
            $table->timestamps();
            
            // One progress record per user per loyalty card
            $table->unique(['loyalty_card_id', 'user_id']);
            $table->index(['user_id', 'loyalty_card_id']);
            $table->index('loyalty_card_id');
        });

        // Create loyalty_card_rewards table - Track claimed free items
        Schema::create('loyalty_card_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_item_id')->nullable()->constrained()->onDelete('set null'); // The free item claimed
            $table->integer('card_completion_number'); // Which completed card this reward is for (1st, 2nd, etc.)
            $table->enum('status', ['pending', 'approved', 'rejected', 'claimed'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['loyalty_card_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        // Add loyalty card tracking to purchases table
        Schema::table('purchases', function (Blueprint $table) {
            $table->boolean('counts_for_loyalty')->default(true)->after('currency_type'); // Whether this purchase counts for loyalty
            $table->foreignId('loyalty_card_id')->nullable()->after('counts_for_loyalty')->constrained()->onDelete('set null'); // Which loyalty card this contributes to
            
            $table->index(['loyalty_card_id', 'counts_for_loyalty']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['loyalty_card_id']);
            $table->dropColumn(['counts_for_loyalty', 'loyalty_card_id']);
        });

        Schema::dropIfExists('loyalty_card_rewards');
        Schema::dropIfExists('loyalty_card_progress');
        Schema::dropIfExists('loyalty_cards');
    }
};