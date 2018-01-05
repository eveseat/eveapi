<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('mail_recipients', function (Blueprint $table) {

            $table->bigInteger('mail_id');
            $table->foreign('mail_id')
                ->references('mail_id')->on('mail_headers');
            $table->integer('recipient_id');
            $table->enum('recipient_type',
                ['alliance', 'character', 'corporation', 'mailing_list']);

            $table->index('mail_id');
            $table->index('recipient_id');
            $table->index('recipient_type');

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

        Schema::dropIfExists('mail_recipients');
    }
}
