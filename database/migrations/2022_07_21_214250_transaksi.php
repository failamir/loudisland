<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('invoice');
            $table->string('event_id');
            $table->string('peserta_id');
            $table->bigInteger('amount');
            $table->text('note')->nullable();
            $table->string('snap_token')->nullable();
            $table->enum('status', array('Pending', 'Success', 'Expired', 'Failed','Refund'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
};
