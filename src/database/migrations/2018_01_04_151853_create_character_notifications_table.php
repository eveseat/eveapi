<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->integer('character_id');
            $table->bigInteger('notification_id');
            $table->string('type');
            $table->integer('sender_id');
            $table->enum('sender_type', ['character', 'corporation', 'alliance', 'faction', 'other']);
            $table->dateTime('timestamp');
            $table->boolean('is_read')->default(false);
            $table->text('text');

            $table->index('character_id');
            $table->index('notification_id');

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

        Schema::dropIfExists('character_notifications');
    }
}
