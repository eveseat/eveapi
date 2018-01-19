<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterAffiliationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_affiliations', function (Blueprint $table) {

            $table->bigInteger('character_id')->primary();
            $table->bigInteger('corporation_id');
            $table->bigInteger('alliance_id')->nullable();
            $table->bigInteger('faction_id')->nullable();

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

        Schema::dropIfExists('character_affiliations');
    }
}
