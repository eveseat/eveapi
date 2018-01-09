<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailMailingListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('mail_mailing_lists', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('mailing_list_id');
            $table->string('name');

            $table->index('character_id');
            $table->index('mailing_list_id');
           
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

        Schema::dropIfExists('mail_mailing_lists');
    }
}
