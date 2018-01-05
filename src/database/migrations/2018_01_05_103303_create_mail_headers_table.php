<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('mail_headers', function (Blueprint $table) {

            $table->integer('character_id');
            $table->bigInteger('mail_id');
            $table->string('subject');
            $table->integer('from');
            $table->dateTime('timestamp');
            $table->json('labels');
            $table->boolean('is_read')->default(false);

            $table->index('character_id');
            $table->index('mail_id');
            $table->index('timestamp');

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

        Schema::dropIfExists('mail_headers');
    }
}
