<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationMemberTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_member_titles', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('character_id');
            $table->integer('title_id');

            $table->primary(['corporation_id', 'character_id', 'title_id'], 'member_titles_primary');
            $table->index('corporation_id');
            $table->index('character_id');

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

        Schema::dropIfExists('corporation_member_titles');
    }
}
