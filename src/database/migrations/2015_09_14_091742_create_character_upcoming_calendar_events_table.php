<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterUpcomingCalendarEventsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_upcoming_calendar_events', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('characterID');
            $table->bigInteger('eventID');
            $table->bigInteger('ownerID');
            $table->string('ownerName')->nullable();
            $table->dateTime('eventDate');
            $table->text('eventTitle');
            $table->integer('duration');
            $table->boolean('importance');
            $table->enum('response', ['Undecided', 'Accepted', 'Declined', 'Tentative']);
            $table->text('eventText');
            $table->integer('ownerTypeID');

            // Indexes
            $table->index('characterID');
            $table->index('eventID');
            $table->index('ownerID');

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

        Schema::drop('character_upcoming_calendar_events');
    }
}
