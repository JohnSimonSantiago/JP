<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // "kiosk" = the Lounge Kiosk account used by a paired /lounge/display screen.
        // It fails every staff/admin check, so its token can only reach the kiosk routes.
        // MySQL requires rebuilding the enum to add a new value.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'shop_owner', 'staff', 'kiosk') DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'user' WHERE role = 'kiosk'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'shop_owner', 'staff') DEFAULT 'user'");
    }
};
