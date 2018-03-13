<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSovereigntyMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('sovereignty_maps', function (Blueprint $table) {

            $table->integer('system_id');
            $table->integer('alliance_id')->nullable();
            $table->bigInteger('corporation_id')->nullable();
            $table->integer('faction_id')->nullable();

            $table->primary('system_id');
            $table->index('corporation_id');
            $table->index('alliance_id');
            $table->index('faction_id');

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

        Schema::dropIfExists('sovereignty_maps');
    }
}
