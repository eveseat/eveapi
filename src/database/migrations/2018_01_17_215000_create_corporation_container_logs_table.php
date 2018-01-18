<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationContainerLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_container_logs', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->dateTime('logged_at');
            $table->bigInteger('container_id');
            $table->integer('container_type_id');
            $table->bigInteger('character_id');
            $table->bigInteger('location_id');
            $table->string('location_flag');
            $table->string('action');
            $table->enum('password_type', ['config', 'general'])->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('old_config_bitmask')->nullable();
            $table->integer('new_config_bitmask')->nullable();

            $table->primary(['corporation_id', 'container_id', 'logged_at'], 'corporation_container_logs_primary_key');
            $table->index('corporation_id');
            $table->index('container_id');
            $table->index('character_id');
            $table->index('location_id');
            $table->index('type_id');

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

        Schema::dropIfExists('corporation_container_logs');
    }
}
