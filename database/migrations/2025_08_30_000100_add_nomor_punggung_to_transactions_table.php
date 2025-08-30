<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'nomor_punggung')) {
                $table->string('nomor_punggung', 32)->nullable()->unique()->after('invoice');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'nomor_punggung')) {
                // drop unique by index name to be safe
                $table->dropUnique('transactions_nomor_punggung_unique');
                $table->dropColumn('nomor_punggung');
            }
        });
    }
};
