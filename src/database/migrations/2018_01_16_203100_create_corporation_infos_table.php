<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_infos', function (Blueprint $table) {

            $table->bigInteger('corporation_id')->primary();
            $table->string('name');
            $table->string('ticker');
            $table->integer('member_count');
            $table->bigInteger('ceo_id');
            $table->integer('alliance_id')->nullable();
            $table->text('description')->nullable();
            $table->float('tax_rate');
            $table->dateTime('date_founded')->nullable();
            $table->bigInteger('creator_id');
            $table->string('url')->nullable();
            $table->integer('faction_id')->nullable();
            $table->integer('home_station_id')->nullable();
            $table->bigInteger('shares')->nullable();

            $table->index('ticker');
            $table->index('ceo_id');
            $table->index('alliance_id');
            $table->index('creator_id');
            $table->index('faction_id');
            $table->index('home_station_id');

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

        Schema::dropIfExists('corporation_infos');
    }
}
