<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEveErrorListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eve_error_lists', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('errorCode')->unique();
            $table->text('errorText');

            // Index
            $table->index('errorCode');

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

        Schema::drop('eve_error_lists');
    }
}
