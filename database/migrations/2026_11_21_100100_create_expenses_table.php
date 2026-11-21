<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            // restrict: a category that has expenses can't be deleted (archive it instead)
            $table->foreignId('expense_category_id')
                ->constrained('expense_categories')
                ->restrictOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('description', 255)->nullable();
            $table->dateTime('spent_at');                    // stored in UTC, shown in Manila time
            $table->string('receipt_path')->nullable();      // PRIVATE disk (storage/app), served by an admin-only route

            // nullable + nullOnDelete: deleting an admin user is never blocked by their expenses
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('spent_at'); // list is sorted and filtered by spent_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
