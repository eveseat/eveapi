<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddUniqueIndexToMailMailingListsTable.
 */
class AddUniqueIndexToMailMailingListsTable extends Migration
{
    public function up()
    {
        Schema::table('mail_mailing_lists', function (Blueprint $table) {
            $table->increments('id');
        });

        DB::statement('DELETE a FROM mail_mailing_lists a INNER JOIN mail_mailing_lists b WHERE a.id > b.id AND a.character_id = b.character_id AND a.mailing_list_id = b.mailing_list_id');

        Schema::table('mail_mailing_lists', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('mail_mailing_lists', function (Blueprint $table) {
            $table->unique(['character_id', 'mailing_list_id'], 'mail_mailing_lists');
        });
    }

    public function down()
    {
        Schema::table('mail_mailing_lists', function (Blueprint $table) {
            $table->dropUnique('mail_mailing_lists');
        });
    }
}
