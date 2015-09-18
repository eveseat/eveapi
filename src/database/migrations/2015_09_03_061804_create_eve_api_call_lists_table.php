<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEveApiCallListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eve_api_call_lists', function (Blueprint $table) {

            $table->increments('id');

            $table->string('type');
            $table->string('name');
            $table->integer('accessMask');
            $table->string('description');

            // Index
            $table->index('type');
            $table->index('name');
            $table->index('accessMask');

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

        Schema::drop('eve_api_call_lists');
    }
}
