<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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
 * Class CreateKillmailsTable.
 */
class CreateKillmailsTable extends Migration
{
    public function up()
    {
        // spawn the new unified killmails table
        Schema::create('killmails', function (Blueprint $table) {
            $table->bigInteger('killmail_id');
            $table->string('killmail_hash');

            $table->timestamps();

            $table->primary('killmail_id');
        });

        // collect mails from deprecated tables
        $killmails = DB::table('character_killmails')
            ->select('killmail_id', 'killmail_hash')
            ->selectRaw('"2020-01-06 00:00:00" as created_at')
            ->selectRaw('"2020-01-06 00:00:00" as updated_at')
            ->union(
                DB::table('corporation_killmails')
                    ->select('killmail_id', 'killmail_hash')
                    ->selectRaw('"2020-01-06 00:00:00" as created_at')
                    ->selectRaw('"2020-01-06 00:00:00" as updated_at')
            )
            ->distinct();

        // populate the new table with historical data
        DB::table('killmails')
            ->insertUsing(['killmail_id', 'killmail_hash', 'created_at', 'updated_at'], $killmails);

        // fix existing foreign keys to cascade
        Schema::table('killmail_attackers', function (Blueprint $table) {
            $table->dropForeign(['killmail_id']);

            $table->foreign('killmail_id')
                ->references('killmail_id')
                ->on('killmail_details')
                ->onDelete('cascade');
        });

        Schema::table('killmail_victims', function (Blueprint $table) {
            $table->dropForeign(['killmail_id']);

            $table->foreign('killmail_id')
                ->references('killmail_id')
                ->on('killmail_details')
                ->onDelete('cascade');
        });

        Schema::table('killmail_victim_items', function (Blueprint $table) {
            $table->dropForeign(['killmail_id']);

            $table->foreign('killmail_id')
                ->references('killmail_id')
                ->on('killmail_details')
                ->onDelete('cascade');
        });

        // remove orphan killmails
        DB::table('killmail_details')
            ->whereNotIn('killmail_id', DB::table('killmails')->select('killmail_id'))
            ->delete();

        // add foreign key between killmail and header
        Schema::table('killmail_details', function (Blueprint $table) {
            $table->foreign('killmail_id')
                ->references('killmail_id')
                ->on('killmails');
        });

        // drop deprecated tables
        Schema::drop('character_killmails');
        Schema::drop('corporation_killmails');
    }

    public function down()
    {
        Schema::create('character_killmails', function (Blueprint $table) {
            $table->bigInteger('character_id');
            $table->bigInteger('killmail_id');
            $table->string('killmail_hash');

            $table->index('character_id');
            $table->index('killmail_id');

            $table->primary(['character_id', 'killmail_id']);

            $table->timestamps();
        });

        Schema::create('corporation_killmails', function (Blueprint  $table) {
            $table->bigInteger('corporation_id');
            $table->bigInteger('killmail_id');
            $table->string('killmail_hash');

            $table->index('corporation_id');
            $table->index('killmail_id');

            $table->primary(['corporation_id', 'killmail_id']);

            $table->timestamps();
        });

        Schema::table('killmail_attackers', function (Blueprint $table) {
            $table->dropForeign(['killmail_id']);

            $table->foreign('killmail_id')
                ->references('killmail_id')
                ->on('killmail_details');
        });

        Schema::table('killmail_victims', function (Blueprint $table) {
            $table->dropForeign(['killmail_id']);

            $table->foreign('killmail_id')
                ->references('killmail_id')
                ->on('killmail_details');
        });

        Schema::table('killmail_victim_items', function (Blueprint $table) {
            $table->dropForeign(['killmail_id']);

            $table->foreign('killmail_id')
                ->references('killmail_id')
                ->on('killmail_details');
        });

        Schema::table('killmail_details', function (Blueprint $table) {
            $table->dropForeign(['killmail_id']);
        });

        Schema::dropIfExists('killmails');
    }
}
