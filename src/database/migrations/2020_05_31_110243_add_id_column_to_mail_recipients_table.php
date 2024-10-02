<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddIdColumnToMailRecipientsTable.
 */
class AddIdColumnToMailRecipientsTable extends Migration
{
    public function up()
    {
        try {
            Schema::table('mail_recipients', function (Blueprint $table) {
                $table->bigIncrements('id')->first();
            });
        } catch (\Illuminate\Database\QueryException $th) {
            // This is a fix for mariadb 10.5 causing issues with migrations due to the CHECK(json_valid('labels'))
            DB::statement('ALTER TABLE mail_recipients MODIFY COLUMN labels longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (JSON_VALID(labels)) AFTER is_read;');
            Schema::table('mail_recipients', function (Blueprint $table) {
                $table->bigIncrements('id')->first();
            });
        }
    }

    public function down()
    {
        Schema::table('mail_recipients', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
}
