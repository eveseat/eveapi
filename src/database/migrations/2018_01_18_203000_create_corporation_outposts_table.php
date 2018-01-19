<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationOutpostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_outposts', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('outpost_id');

            $table->primary(['corporation_id', 'outpost_id'], 'corporation_outposts_primary_key');
            $table->index('corporation_id');
            $table->index('outpost_id');

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

        Schema::dropIfExists('corporation_outposts');
    }
}
