<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterCalendarEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_calendar_events', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('event_id');
            $table->dateTime('event_date');
            $table->string('title');
            $table->integer('importance');
            $table->enum('event_response', ['declined', 'not_responded', 'accepted', 'tentative']);

            $table->primary(['character_id', 'event_id']);
            $table->index('event_date');

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

        Schema::dropIfExists('character_calendar_events');
    }
}
