<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterMailMessagesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_mail_messages', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('characterID');
            $table->integer('messageID');
            $table->integer('senderID');
            $table->string('senderName');
            $table->dateTime('sentDate');
            $table->string('title');
            $table->integer('toCorpOrAllianceID')->nullable();
            $table->text('toCharacterIDs')->nullable();
            $table->integer('toListID')->nullable();

            // Indexes
            $table->index('characterID');
            $table->index('messageID');
            $table->index('senderID');

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

        Schema::drop('character_mail_messages');
    }
}
