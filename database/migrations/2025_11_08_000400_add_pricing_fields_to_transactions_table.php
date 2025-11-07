<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('transactions', 'service_fee')) {
                $table->decimal('service_fee', 12, 2)->default(0)->after('discount');
            }
            if (!Schema::hasColumn('transactions', 'ppn')) {
                $table->decimal('ppn', 12, 2)->default(0)->after('service_fee');
            }
            if (!Schema::hasColumn('transactions', 'final_price')) {
                $table->decimal('final_price', 12, 2)->nullable()->after('ppn');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'final_price')) {
                $table->dropColumn('final_price');
            }
            if (Schema::hasColumn('transactions', 'ppn')) {
                $table->dropColumn('ppn');
            }
            if (Schema::hasColumn('transactions', 'service_fee')) {
                $table->dropColumn('service_fee');
            }
            if (Schema::hasColumn('transactions', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }
};
