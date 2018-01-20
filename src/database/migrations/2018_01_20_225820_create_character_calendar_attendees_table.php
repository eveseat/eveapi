<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterCalendarAttendeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_calendar_attendees', function (Blueprint $table) {

            $table->bigInteger('event_id');
            $table->bigInteger('character_id');
            $table->enum('event_response', ['declined', 'not_responded', 'accepted', 'tentative']);

            $table->primary(['event_id', 'character_id']);
            $table->index('event_id');
            $table->index('character_id');

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

        Schema::dropIfExists('character_calendar_attendees');
    }
}
