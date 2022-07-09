<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPendaftarsTable extends Migration
{
    public function up()
    {
        Schema::table('pendaftars', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable();
            $table->foreign('event_id', 'event_fk_6953391')->references('id')->on('events');
        });
    }
}
