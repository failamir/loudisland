<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            // who handled racepack exchange (staff identifier, e.g. user id or name)
            $table->string('racepack_by')->nullable()->after('status_racepack');
            // timestamp when racepack was taken
            $table->timestamp('racepack_at')->nullable()->after('racepack_by');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['racepack_by', 'racepack_at']);
        });
    }
};
