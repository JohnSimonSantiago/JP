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
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('image_url');
            $table->string('image_path')->nullable(); // Local file path if stored locally
            $table->integer('display_order')->default(0); // For ordering images
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin who created it
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who last updated
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['is_active', 'display_order']);
            $table->index('created_by');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};