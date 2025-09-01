<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'uid')) {
                $table->string('uid')->nullable()->after('deleted_at');
            }
            if (!Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 100)->nullable()->after('uid');
            }
            if (!Schema::hasColumn('users', 'no_hp')) {
                $table->string('no_hp', 100)->nullable()->after('nik');
            }

            if (!Schema::hasColumn('users', 'no_tiket')) {
                $table->string('no_tiket')->nullable()->after('no_hp');
            }
            // city/region/village already exist in your users table; only add if missing
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('no_tiket');
            }
            if (!Schema::hasColumn('users', 'region')) {
                $table->string('region')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'village')) {
                $table->string('village')->nullable()->after('region');
            }
            if (!Schema::hasColumn('users', 'checkin')) {
                $table->string('checkin')->nullable()->after('village');
            }
            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('checkin');
            }
            if (!Schema::hasColumn('users', 'nomor_punggung')) {
                $table->string('nomor_punggung')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('users', 'status_payment')) {
                $table->string('status_payment')->nullable()->after('nomor_punggung');
            }
            if (!Schema::hasColumn('users', 'payment_type')) {
                $table->string('payment_type')->nullable()->after('status_payment');
            }
            if (!Schema::hasColumn('users', 'total_bayar')) {
                $table->decimal('total_bayar', 15, 2)->nullable()->after('payment_type');
            }
            if (!Schema::hasColumn('users', 'event_id')) {
                // use unsignedBigInteger with index to avoid cross-DB FK issues
                $table->unsignedBigInteger('event_id')->nullable()->after('total_bayar');
                // If you are sure events table exists and FK is desired, uncomment below:
                // $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'start_at')) {
                $table->dateTime('start_at')->nullable()->after('event_id');
            }
            if (!Schema::hasColumn('users', 'finish_at')) {
                $table->dateTime('finish_at')->nullable()->after('start_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'uid','nik','no_hp','no_tiket','city','region','village','checkin','notes',
                'nomor_punggung','status_payment','payment_type','total_bayar','event_id','start_at','finish_at'
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    try { $table->dropColumn($col); } catch (\Throwable $e) {}
                }
            }
        });
    }
};
