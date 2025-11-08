<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'referral_code_id')) {
                $table->unsignedBigInteger('referral_code_id')->nullable()->after('promo_code_id');
                $table->index('referral_code_id', 'transactions_referral_code_id_index');
            }
        });

        // Add FK constraint separately to avoid issues when table not exists yet in some envs
        Schema::table('transactions', function (Blueprint $table) {
            try {
                $table->foreign('referral_code_id')
                    ->references('id')->on('referral_codes')
                    ->nullOnDelete();
            } catch (\Throwable $e) {
                // ignore if cannot add constraint (e.g., driver limitations); index still helps
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            try {
                $table->dropForeign(['referral_code_id']);
            } catch (\Throwable $e) {}
            if (Schema::hasColumn('transactions', 'referral_code_id')) {
                $table->dropIndex('transactions_referral_code_id_index');
                $table->dropColumn('referral_code_id');
            }
        });
    }
};
