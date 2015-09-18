<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMapJumpsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('map_jumps', function (Blueprint $table) {

            $table->integer('solarSystemID')->unique();
            $table->integer('shipJumps');

            // Indexes
            $table->primary('solarSystemID');

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

        Schema::drop('map_jumps');
    }
}
