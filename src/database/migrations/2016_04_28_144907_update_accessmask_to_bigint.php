<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

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
