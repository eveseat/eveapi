<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationAllianceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_alliance_histories', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->integer('record_id');
            $table->dateTime('start_date');
            $table->integer('alliance_id')->nullable();
            $table->boolean('is_deleted')->default(false);

            $table->primary(['corporation_id', 'record_id']);
            $table->index('corporation_id');

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

        Schema::dropIfExists('corporation_alliance_histories');
    }
}
