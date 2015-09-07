<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacterContactNotificationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_contact_notifications', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('notificationID');
            $table->integer('senderID');
            $table->string('senderName');
            $table->dateTime('sentDate');
            $table->string('messageData');

            // Indexes
            $table->index('characterID');
            $table->index('notificationID');
            $table->index('sentDate');

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

        Schema::drop('character_contact_notifications');
    }
}
