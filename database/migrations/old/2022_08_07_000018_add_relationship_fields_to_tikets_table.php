<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToTiketsTable extends Migration
{
    public function up()
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->unsignedBigInteger('peserta_id')->nullable();
            $table->foreign('peserta_id', 'peserta_fk_7114415')->references('id')->on('users');
        });
    }
}
