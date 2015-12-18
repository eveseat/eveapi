<?php

use Illuminate\Database\Migrations\Migration;

class AddCharacterBookmarkMapInfo extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('character_bookmarks', function ($table) {

            $table->string('mapName')->after('z')->nullable();
            $table->integer('mapID')->after('z')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('character_bookmarks', function ($table) {

            $table->dropColumn(['mapName', 'mapID']);
        });
    }
}
