<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

class DropCorporationBookmarkFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('corporation_bookmark_folders');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('corporation_bookmark_folders', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('folder_id');
            $table->string('name');
            $table->bigInteger('creator_id')->nullable();

            $table->primary(['folder_id']);
            $table->index('corporation_id', 'corporation_bookmark_folders_corporation_id_index');
            $table->index('folder_id', 'corporation_bookmark_folders_folder_id_index');

            $table->timestamps();

            $table->foreign('corporation_id')
                ->references('corporation_id')
                ->on('corporation_infos')
                ->onDelete('CASCADE');
        });
    }
}
