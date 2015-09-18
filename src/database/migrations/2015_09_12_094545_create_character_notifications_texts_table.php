<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterNotificationsTextsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_notifications_texts', function (Blueprint $table) {

            $table->integer('notificationID')->unique();
            $table->text('text');

            // Indexes
            $table->primary('notificationID');

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

        Schema::drop('character_notifications_texts');
    }
}
