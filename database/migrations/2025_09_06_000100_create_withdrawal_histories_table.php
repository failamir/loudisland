<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdrawal_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('withdrawal_id');
            $table->string('action'); // created, approved, paid, rejected, canceled
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('acted_by_id')->nullable();
            $table->unsignedBigInteger('amount_snapshot')->default(0);
            $table->unsignedBigInteger('balance_before')->default(0);
            $table->unsignedBigInteger('balance_after')->default(0);
            $table->timestamps();

            $table->foreign('withdrawal_id')->references('id')->on('withdrawals')->onDelete('cascade');
            $table->foreign('acted_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_histories');
    }
};
