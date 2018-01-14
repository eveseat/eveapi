<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->bigInteger('corporation_id');
            $table->bigInteger('bookmark_id');
            $table->bigInteger('folder_id')->nullable();
            $table->dateTime('created');
            $table->string('label');
            $table->text('notes');
            $table->bigInteger('location_id');
            $table->bigInteger('creator_id');

            // item
            $table->bigInteger('item_id')->nullable();
            $table->integer('type_id')->nullable();

            // coordinates
            $table->double('x')->nullable();
            $table->double('y')->nullable();
            $table->double('z')->nullable();
            $table->bigInteger('map_id')->nullable();
            $table->string('map_name')->nullable();

            $table->primary(['corporation_id', 'bookmark_id']);
            $table->index('corporation_id');
            $table->index('folder_id');
            $table->index('creator_id');

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

        Schema::dropIfExists('corporation_bookmarks');
    }
}
