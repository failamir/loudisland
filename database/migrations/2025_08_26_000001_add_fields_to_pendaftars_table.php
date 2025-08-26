<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftars', function (Blueprint $table) {
            if (!Schema::hasColumn('pendaftars', 'province')) {
                $table->string('province')->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('pendaftars', 'city')) {
                $table->string('city')->nullable()->after('province');
            }
            if (!Schema::hasColumn('pendaftars', 'address')) {
                $table->string('address')->nullable()->after('city');
            }
            if (!Schema::hasColumn('pendaftars', 'nomor_punggung')) {
                $table->string('nomor_punggung')->nullable()->after('no_tiket');
            }
            if (!Schema::hasColumn('pendaftars', 'start_at')) {
                $table->dateTime('start_at')->nullable()->after('checkin');
            }
            if (!Schema::hasColumn('pendaftars', 'finish_at')) {
                $table->dateTime('finish_at')->nullable()->after('start_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pendaftars', function (Blueprint $table) {
            if (Schema::hasColumn('pendaftars', 'province')) {
                $table->dropColumn('province');
            }
            if (Schema::hasColumn('pendaftars', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('pendaftars', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('pendaftars', 'nomor_punggung')) {
                $table->dropColumn('nomor_punggung');
            }
            if (Schema::hasColumn('pendaftars', 'start_at')) {
                $table->dropColumn('start_at');
            }
            if (Schema::hasColumn('pendaftars', 'finish_at')) {
                $table->dropColumn('finish_at');
            }
        });
    }
};
