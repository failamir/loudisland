<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Change amount to decimal(15,2) if column exists and is not already decimal
            if (Schema::hasColumn('transactions', 'amount')) {
                // Use raw statement to avoid platform-specific altering issues
                DB::statement("ALTER TABLE transactions MODIFY amount DECIMAL(15,2) NULL");
            }
        });

        // Convert tiket_id to unsignedBigInteger and add unique FK to tikets.id
        // This requires safe transition because existing tiket_id might be string
        if (Schema::hasTable('transactions')) {
            // Create a temp column, copy, then drop old and rename
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'tiket_id_tmp')) {
                    $table->unsignedBigInteger('tiket_id_tmp')->nullable()->after('event_id');
                }
            });

            // Best-effort copy numeric values
            DB::statement("UPDATE transactions SET tiket_id_tmp = NULLIF(tiket_id, '') + 0 WHERE tiket_id REGEXP '^[0-9]+$'");

            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'tiket_id')) {
                    $table->dropColumn('tiket_id');
                }
            });

            // Create final column and copy over values, then drop temp
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'tiket_id')) {
                    $table->unsignedBigInteger('tiket_id')->nullable()->after('event_id');
                }
            });

            DB::statement("UPDATE transactions SET tiket_id = tiket_id_tmp");

            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'tiket_id_tmp')) {
                    $table->dropColumn('tiket_id_tmp');
                }
            });

            Schema::table('transactions', function (Blueprint $table) {
                // Ensure index/constraint
                if (!Schema::hasColumn('transactions', 'tiket_id')) return;
                // Unique to enforce 1 transaksi per tiket
                $table->unique('tiket_id', 'transactions_tiket_id_unique');
                // Add FK if tikets table exists
                if (Schema::hasTable('tikets')) {
                    $table->foreign('tiket_id')->references('id')->on('tikets')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // Drop unique/foreign and convert back (non-destructive best-effort)
        Schema::table('transactions', function (Blueprint $table) {
            try {
                $table->dropForeign(['tiket_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropUnique('transactions_tiket_id_unique');
            } catch (\Throwable $e) {
            }
        });
        // Convert back to string column named tiket_id (keeps data by casting to string)
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('tiket_id_str')->nullable()->after('event_id');
        });
        DB::statement("UPDATE transactions SET tiket_id_str = tiket_id");
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('tiket_id');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('tiket_id_str', 'tiket_id');
        });
        // Revert amount to string
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'amount')) {
                DB::statement("ALTER TABLE transactions MODIFY amount VARCHAR(255) NULL");
            }
        });
    }
};
