<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Starter expense categories. Safe to run more than once: existing names are left alone.
//   docker exec Jp-Php php artisan db:seed --class=ExpenseCategorySeeder
class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Rent',                'color' => '#6366f1'], // indigo
            ['name' => 'Electricity',         'color' => '#f59e0b'], // amber
            ['name' => 'Internet',            'color' => '#8b5cf6'], // violet
            ['name' => 'Salaries',            'color' => '#22c55e'], // green
            ['name' => 'Repairs/Maintenance', 'color' => '#ef4444'], // red
            ['name' => 'Equipment',           'color' => '#64748b'], // slate
        ];

        $now = now();
        foreach ($categories as $c) {
            $exists = DB::table('expense_categories')->where('name', $c['name'])->exists();
            if (!$exists) {
                DB::table('expense_categories')->insert($c + [
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
