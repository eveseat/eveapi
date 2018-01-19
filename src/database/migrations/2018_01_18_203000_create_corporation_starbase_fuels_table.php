<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationStarbaseFuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_starbase_fuels', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('starbase_id');
            $table->integer('type_id');
            $table->integer('quantity');

            $table->primary(['corporation_id', 'starbase_id', 'type_id'],
                'corporation_starbase_fuels_primary_key');
            $table->index('corporation_id');
            $table->index('starbase_id');

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

        Schema::dropIfExists('corporation_starbase_fuels');
    }
}
