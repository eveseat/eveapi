<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterNotificationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_notifications', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('characterID');
            $table->integer('notificationID');

            $table->integer('typeID');
            $table->integer('senderID');
            $table->string('senderName');
            $table->dateTime('sentDate');
            $table->integer('read');

            // Indexes
            $table->index('characterID');
            $table->index('notificationID');

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

        Schema::drop('character_notifications');
    }
}
