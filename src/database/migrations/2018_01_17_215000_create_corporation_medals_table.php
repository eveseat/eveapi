<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationMedalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_medals', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->integer('medal_id');
            $table->string('title');
            $table->text('description');
            $table->bigInteger('creator_id');

            $table->primary(['corporation_id', 'medal_id'], 'corporation_medals_primary_key');
            $table->index('corporation_id');
            $table->index('medal_id');
            $table->index('creator_id');

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

        Schema::dropIfExists('corporation_medals');
    }
}
