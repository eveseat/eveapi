<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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

            $table->integer('errorCode')->unique();
            $table->text('errorText');

            // Index
            $table->primary('errorCode');

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
