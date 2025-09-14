<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'expired_snap_time')) {
                $table->timestamp('expired_snap_time')->nullable()->after('snap_token');
            }
        });

        // Backfill existing rows: set to created_at + 15 minutes if null
        try {
            DB::statement("UPDATE transactions SET expired_snap_time = DATE_ADD(created_at, INTERVAL 15 MINUTE) WHERE expired_snap_time IS NULL");
        } catch (\Throwable $e) {
            // ignore if the platform doesn't support DATE_ADD (should work on MySQL/MariaDB)
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'expired_snap_time')) {
                $table->dropColumn('expired_snap_time');
            }
        });
    }
};
