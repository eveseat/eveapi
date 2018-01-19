<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationOutpostServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_outpost_services', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('outpost_id');
            $table->string('service_name');
            $table->double('minimum_standing');
            $table->double('surcharge_per_bad_standing');
            $table->double('discount_per_good_standing');

            $table->primary(['corporation_id', 'outpost_id', 'service_name'],
                'corporation_outposts_primary_key');
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

        Schema::dropIfExists('corporation_outpost_services');
    }
}
