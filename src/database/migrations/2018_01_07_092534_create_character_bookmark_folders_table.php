<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterBookmarkFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_bookmark_folders', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('folder_id');
            $table->string('name');

            $table->primary(['character_id', 'folder_id']);

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

        Schema::dropIfExists('character_bookmark_folders');
    }
}
