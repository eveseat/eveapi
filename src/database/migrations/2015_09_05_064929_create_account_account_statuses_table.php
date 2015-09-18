<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountAccountStatusesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('account_account_statuses', function (Blueprint $table) {

            $table->integer('keyID')->unique();

            $table->dateTime('paidUntil');
            $table->dateTime('createDate');
            $table->integer('logonCount');
            $table->integer('logonMinutes');

            // Indexes
            $table->primary('keyID');

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

        Schema::drop('account_account_statuses');
    }
}
