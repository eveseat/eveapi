<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterMailMessageBodiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_mail_message_bodies', function (Blueprint $table) {

            $table->integer('messageID')->unique();
            $table->text('body');

            // Indexes
            $table->primary('messageID');

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

        Schema::drop('character_mail_message_bodies');
    }
}
