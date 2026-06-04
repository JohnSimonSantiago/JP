<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires rebuilding the enum to add a new value
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'shop_owner', 'staff') DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'shop_owner') DEFAULT 'user'");
    }
};