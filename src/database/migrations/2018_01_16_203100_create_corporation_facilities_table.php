<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_facilities', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('facility_id');
            $table->integer('system_id');
            $table->integer('type_id');

            $table->primary(['corporation_id', 'facility_id'], 'corporation_facilities_primary_key');
            $table->index('corporation_id');
            $table->index('facility_id');
            $table->index('system_id');
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

        Schema::dropIfExists('corporation_facilities');
    }
}
