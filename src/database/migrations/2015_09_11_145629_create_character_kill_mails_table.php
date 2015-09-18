<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterKillMailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_kill_mails', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('killID');

            // Indexes
            $table->index('characterID');
            $table->index('killID');

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

        Schema::drop('character_kill_mails');
    }
}
