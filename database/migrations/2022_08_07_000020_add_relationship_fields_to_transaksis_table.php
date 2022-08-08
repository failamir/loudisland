<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToTransaksisTable extends Migration
{
    public function up()
    {
        Schema::table('transaksis', function (Blueprint $table) {
            // $table->unsignedBigInteger('event_id')->nullable();
            // $table->foreign('event_id', 'event_fk_7114420')->references('id')->on('events');
            // $table->unsignedBigInteger('tiket_id')->nullable();
            // $table->foreign('tiket_id', 'tiket_fk_7114421')->references('id')->on('tikets');
            $table->unsignedBigInteger('peserta_id')->nullable();
            $table->foreign('peserta_id', 'peserta_fk_7114422')->references('id')->on('users');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->foreign('created_by_id', 'created_by_fk_7114430')->references('id')->on('users');
        });
    }
}
