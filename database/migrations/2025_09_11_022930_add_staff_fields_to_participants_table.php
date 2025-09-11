<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'staff_user_id')) {
                $table->unsignedBigInteger('staff_user_id')->nullable()->after('status_racepack');
            }
            if (!Schema::hasColumn('participants', 'racepack_by')) {
                $table->string('racepack_by')->nullable()->after('staff_user_id');
            }
            if (!Schema::hasColumn('participants', 'racepack_at')) {
                $table->timestamp('racepack_at')->nullable()->after('racepack_by');
            }

            if (Schema::hasColumn('participants', 'staff_user_id')) {
                $table->foreign('staff_user_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (Schema::hasColumn('participants', 'staff_user_id')) {
                $table->dropForeign(['staff_user_id']);
                $table->dropColumn('staff_user_id');
            }
            if (Schema::hasColumn('participants', 'racepack_by')) {
                $table->dropColumn('racepack_by');
            }
            if (Schema::hasColumn('participants', 'racepack_at')) {
                $table->dropColumn('racepack_at');
            }
        });
    }
};
