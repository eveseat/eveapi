<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_structures', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('structure_id');
            $table->integer('type_id');
            $table->integer('system_id');
            $table->integer('profile_id');
            $table->dateTime('fuel_expires')->nullable();
            $table->dateTime('state_timer_start')->nullable();
            $table->dateTime('state_timer_end')->nullable();
            $table->dateTime('unanchors_at')->nullable();

            $table->primary(['corporation_id', 'structure_id'], 'corporation_structures_primary_key');
            $table->index('corporation_id');
            $table->index('structure_id');
            $table->index('system_id');
            $table->index('profile_id');
            $table->index('type_id');

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

        Schema::dropIfExists('corporation_structures');
    }
}
