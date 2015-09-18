<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterBookmarksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_bookmarks', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('characterID');
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
            $table->string('memo');
            $table->text('note');

            // Indexes
            $table->index('characterID');

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

        Schema::drop('character_bookmarks');
    }
}
