<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterCalendarEventDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_calendar_event_details', function (Blueprint $table) {

            $table->integer('event_id')->primary();
            $table->integer('owner_id');
            $table->string('owner_name');
            $table->integer('duration');
            // ignoring some columns that is in the events table
            $table->text('text');
            $table->enum('owner_type',
                ['eve_server', 'corporation', 'faction', 'character', 'alliance']);

            $table->index('owner_id');
            $table->index('owner_name');

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

        Schema::dropIfExists('character_calendar_event_details');
    }
}
