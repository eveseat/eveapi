<?php

use Illuminate\Database\Migrations\Migration;

class UpdateAccessmaskToBigint extends Migration
{

    /**
     * Run the migrations.
     *
     * Using DB::statement() here cause:
     *  https://laravel.com/docs/5.1/migrations#modifying-columns
     *  "Note: Renaming columns in a table with a enum column is not currently supported."
     *
     * @return void
     */
    public function up()
    {

        DB::statement('ALTER TABLE `account_api_key_infos` CHANGE `accessMask` `accessMask` BIGINT  NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::statement('ALTER TABLE `account_api_key_infos` CHANGE `accessMask` `accessMask` INT  NOT NULL');
    }
}
