<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Each member's unused spins (like consumable_minutes, but for spins)
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('spin_credits')->default(0)->after('consumable_minutes');
        });

        // 2. "Receipt stamp": how many spins this purchase actually gave
        Schema::table('consumable_purchases', function (Blueprint $table) {
            $table->unsignedInteger('spins_awarded')->default(0)->after('minutes_added');
        });

        // 3. Wheel settings — always a single row
        Schema::create('coin_wheel_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            // [{ "amount": 5, "chance": 94, "slices": 20 }, ...]
            $table->json('prizes');
            $table->timestamps();
        });

        // Starts OFF so nothing changes until you flip the switch (that's "launch")
        DB::table('coin_wheel_settings')->insert([
            'is_enabled' => false,
            'prizes'     => json_encode([
                ['amount' => 5,  'chance' => 94, 'slices' => 20],
                ['amount' => 10, 'chance' => 5,  'slices' => 2],
                ['amount' => 15, 'chance' => 1,  'slices' => 1],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Spin history (replaces storage/app/coin-spins.jsonl)
        Schema::create('coin_spins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('member_name');
            $table->unsignedInteger('amount');
            $table->decimal('new_cash', 10, 2)->default(0);
            // Only filled for old admin-made spins; member spins leave these empty
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admin_name')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        // 5. Bonus spins given by admin (negative = correction)
        Schema::create('spin_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('spins');
            $table->string('reason')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $this->importOldSpins();
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_grants');
        Schema::dropIfExists('coin_spins');
        Schema::dropIfExists('coin_wheel_settings');

        Schema::table('consumable_purchases', function (Blueprint $table) {
            $table->dropColumn('spins_awarded');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('spin_credits');
        });
    }

    /**
     * One-time copy of the old file-based history into coin_spins.
     * The original file is left untouched as a backup.
     */
    private function importOldSpins(): void
    {
        $path = storage_path('app/coin-spins.jsonl');
        if (!file_exists($path)) {
            return;
        }

        $userIds = DB::table('users')->pluck('id')->flip();
        $rows = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $s = json_decode($line, true);
            if (!$s || !isset($s['amount'])) {
                continue;
            }

            // Old entries were saved in Manila time; the database stores UTC
            $at = Carbon::parse($s['created_at'])->utc()->format('Y-m-d H:i:s');

            $rows[] = [
                'user_id'     => isset($userIds[$s['member_id'] ?? 0]) ? $s['member_id'] : null,
                'member_name' => $s['member_name'] ?? 'Unknown',
                'amount'      => (int) $s['amount'],
                'new_cash'    => $s['new_cash'] ?? 0,
                'admin_id'    => isset($userIds[$s['admin_id'] ?? 0]) ? $s['admin_id'] : null,
                'admin_name'  => $s['admin_name'] ?? null,
                'created_at'  => $at,
                'updated_at'  => $at,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('coin_spins')->insert($chunk);
        }
    }
};