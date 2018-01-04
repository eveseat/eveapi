<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterCorporationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_corporation_histories', function (Blueprint $table) {

            $table->integer('character_id');

            $table->dateTime('start_date');
            $table->integer('corporation_id');
            $table->boolean('is_deleted')->default(false);
            $table->integer('record_id');

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

        Schema::dropIfExists('character_corporation_histories');
    }
}
