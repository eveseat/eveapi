<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationBookmarksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_bookmarks', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('corporationID');
            $table->integer('folderID');
            $table->string('folderName');
            $table->integer('bookmarkID');
            $table->integer('creatorID');
            $table->dateTime('created');
            $table->integer('itemID');
            $table->integer('typeID');
            $table->integer('locationID');
            $table->double('x');
            $table->double('y');
            $table->double('z');
            $table->string('mapName')->nullable();
            $table->integer('mapID')->nullable();
            $table->string('memo');
            $table->text('note');

            // Indexes
            $table->index('corporationID');

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

        Schema::drop('corporation_bookmarks');
    }
}
