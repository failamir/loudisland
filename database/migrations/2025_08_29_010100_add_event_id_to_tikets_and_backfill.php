<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add event_id to tikets if missing
        Schema::table('tikets', function (Blueprint $table) {
            if (!Schema::hasColumn('tikets', 'event_id')) {
                $table->unsignedBigInteger('event_id')->nullable()->after('notes');
                $table->index('event_id', 'tikets_event_id_idx');
            }
            // add indexes helpful for queries
            if (!Schema::hasColumn('tikets', 'peserta_id')) {
                // column exists per earlier migration, skip
            } else {
                $table->index('peserta_id', 'tikets_peserta_id_idx');
            }
        });

        // Backfill from transactions.event_id where possible
        if (Schema::hasTable('transactions')) {
            // Prefer value from transactions linked by tiket_id
            DB::statement(<<<SQL
                UPDATE tikets t
                JOIN transactions tr ON tr.tiket_id = t.id AND tr.event_id IS NOT NULL
                SET t.event_id = tr.event_id
                WHERE t.event_id IS NULL
            SQL);
        }
        // Fallback: from users.event_id if present
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'event_id')) {
            DB::statement(<<<SQL
                UPDATE tikets t
                JOIN users u ON u.id = t.peserta_id AND u.event_id IS NOT NULL
                SET t.event_id = u.event_id
                WHERE t.event_id IS NULL
            SQL);
        }

        // Add FK if events table exists
        Schema::table('tikets', function (Blueprint $table) {
            if (Schema::hasTable('events')) {
                try {
                    $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
                } catch (\Throwable $e) {
                    // ignore if already exists
                }
            }
        });

        // Add indexes to transactions for performance and constraints
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'tiket_id')) {
                // unique already added in previous migration, ensure index for peserta_id/invoice
                $table->index('peserta_id', 'transactions_peserta_id_idx');
                $table->unique('invoice', 'transactions_invoice_unique');
            }
        });

        // Helpful indexes on users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email')) {
                $table->index('email', 'users_email_idx');
            }
            if (Schema::hasColumn('users', 'nik')) {
                $table->index('nik', 'users_nik_idx');
            }
            if (Schema::hasColumn('users', 'no_hp')) {
                $table->index('no_hp', 'users_no_hp_idx');
            }
        });
    }

    public function down(): void
    {
        // Remove constraints and indexes
        Schema::table('tikets', function (Blueprint $table) {
            try { $table->dropForeign(['event_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex('tikets_event_id_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('tikets_peserta_id_idx'); } catch (\Throwable $e) {}
            try { $table->dropColumn('event_id'); } catch (\Throwable $e) {}
        });
        Schema::table('transactions', function (Blueprint $table) {
            try { $table->dropIndex('transactions_peserta_id_idx'); } catch (\Throwable $e) {}
            try { $table->dropUnique('transactions_invoice_unique'); } catch (\Throwable $e) {}
        });
        Schema::table('users', function (Blueprint $table) {
            try { $table->dropIndex('users_email_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('users_nik_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('users_no_hp_idx'); } catch (\Throwable $e) {}
        });
    }
};
