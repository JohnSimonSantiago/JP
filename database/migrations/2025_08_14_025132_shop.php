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
        // Create shops table
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false); // Admin verification
            $table->json('settings')->nullable(); // Shop customization settings
            $table->timestamps();
            
            $table->index(['is_active', 'is_verified']);
            $table->index('owner_id');
        });

        // Add shop_id to shop_items table (NO CATEGORY)
        Schema::table('shop_items', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            
            // Add new indexes
            $table->index(['shop_id', 'is_active']);
        });

        // Update users table to include shop_owner role
        Schema::table('users', function (Blueprint $table) {
            // Check if role column exists before trying to drop it
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin', 'shop_owner'])->default('user')->after('is_premium');
        });

        // Create shop_reviews table for customer feedback
        Schema::create('shop_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating'); // Remove between constraint as it might not work in all DB versions
            $table->text('comment')->nullable();
            $table->timestamps();
            
            $table->unique(['shop_id', 'user_id']); // One review per user per shop
            $table->index('shop_id');
        });

        // Create shop_followers table for users to follow shops
        Schema::create('shop_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['shop_id', 'user_id']);
            $table->index('shop_id');
            $table->index('user_id');
        });

        // Update purchases table to include shop_id for easier tracking
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('shop_item_id')->constrained()->onDelete('cascade');
            $table->string('rejection_reason')->nullable()->after('status');
            
            $table->index(['shop_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn(['shop_id', 'rejection_reason']);
        });

        Schema::dropIfExists('shop_followers');
        Schema::dropIfExists('shop_reviews');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin'])->default('user')->after('is_premium');
        });

        Schema::table('shop_items', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn('shop_id');
        });

        Schema::dropIfExists('shops');
    }
};