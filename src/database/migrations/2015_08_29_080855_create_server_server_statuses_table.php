<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServerServerStatusesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('server_server_statuses', function (Blueprint $table) {

            $table->increments('id');

            $table->dateTime('currentTime');
            $table->string('serverOpen');
            $table->integer('onlinePlayers');

            $table->index('currentTime');
            $table->index('onlinePlayers');

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

        Schema::drop('server_server_statuses');
    }
}
