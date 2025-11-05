<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id_referral')->nullable()->index();
            $table->string('kode')->index();
            $table->decimal('value', 12, 2)->default(0);
            $table->timestamp('tanggal')->useCurrent();
            $table->string('email_pemesan')->nullable()->index();
            $table->timestamps();

            $table->foreign('user_id_referral')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referal');
    }
};
