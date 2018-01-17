<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationIssuedMedalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_issued_medals', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->integer('medal_id');
            $table->bigInteger('character_id');
            $table->text('reason');
            $table->enum('status', ['private', 'public']);
            $table->bigInteger('issuer_id');
            $table->dateTime('issued_at');

            $table->primary(['corporation_id', 'medal_id', 'character_id'], 'corporation_issued_medals_primary_key');
            $table->index('corporation_id');
            $table->index('medal_id');
            $table->index('character_id');
            $table->index('issuer_id');

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

        Schema::dropIfExists('corporation_issued_medals');
    }
}
