<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('mail_labels', function (Blueprint $table) {

            $table->integer('character_id');
            $table->integer('label_id');
            $table->string('name')->nullable();
            $table->string('color')->nullable();

            $table->index('character_id');
            $table->index('label_id');

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

        Schema::dropIfExists('mail_labels');
    }
}
