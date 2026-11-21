<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Recurring templates: saved presets for monthly bills. They NEVER create expenses
// by themselves; they only pre-fill the Add Expense form ("Log it").
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_templates', function (Blueprint $table) {
            $table->id();

            // restrict: a category used by a template can't be deleted
            $table->foreignId('expense_category_id')
                ->constrained('expense_categories')
                ->restrictOnDelete();

            $table->string('description', 255)->nullable();
            $table->decimal('default_amount', 10, 2)->nullable(); // empty for bills that change (electricity)
            $table->unsignedTinyInteger('due_day');               // 1–31; 31 in a 30-day month = last day
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('expenses', function (Blueprint $table) {
            // which template an expense was logged from; deleting the template keeps the expense
            $table->foreignId('expense_template_id')
                ->nullable()
                ->after('expense_category_id')
                ->constrained('expense_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_template_id');
        });
        Schema::dropIfExists('expense_templates');
    }
};
