<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationBookmarkFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_bookmark_folders', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('folder_id');
            $table->string('name');
            $table->bigInteger('creator_id')->nullable();

            $table->primary(['corporation_id', 'folder_id']);
            $table->index('corporation_id');
            $table->index('folder_id');

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

        Schema::dropIfExists('corporation_bookmark_folders');
    }
}
