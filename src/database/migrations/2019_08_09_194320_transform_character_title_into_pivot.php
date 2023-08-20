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
 * Class TransformCharacterTitleIntoPivot.
 */
class TransformCharacterTitleIntoPivot extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::rename('character_titles', 'character_info_corporation_title');

        Schema::table('character_info_corporation_title', function (Blueprint $table) {
            $table->renameColumn('character_id', 'character_info_character_id');
            $table->renameColumn('title_id', 'corporation_title_id');
            $table->dropColumn('name');
            $table->dropTimestamps();
            $table->unique(['character_info_character_id', 'corporation_title_id'], 'character_corporation_title');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::rename('character_info_corporation_title', 'character_titles');

        Schema::table('character_titles', function (Blueprint $table) {
            $table->string('name')->after('corporation_title_id')->nullable();
            $table->timestamps();
            $table->renameColumn('character_info_character_id', 'character_id');
            $table->renameColumn('corporation_title_id', 'title_id');
            $table->dropUnique('character_corporation_title');
        });

        Schema::enableForeignKeyConstraints();
    }
}
