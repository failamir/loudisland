<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksisTable extends Migration
{
    public function up()
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice')->nullable();
            $table->string('event_id')->nullable();
            $table->string('tiket_id')->nullable();
            $table->string('amount')->nullable();
            $table->longText('note')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
