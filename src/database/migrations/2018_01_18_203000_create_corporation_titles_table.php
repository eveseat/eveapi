<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_titles', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->integer('title_id');
            $table->string('name');

            $table->primary(['corporation_id', 'title_id'], 'corporation_titles_primary_key');
            $table->index('corporation_id');
            $table->index('title_id');

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

        Schema::dropIfExists('corporation_titles');
    }
}
