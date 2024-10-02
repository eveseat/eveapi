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

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveCorporationIdColumnFromCorporationTitleRolesTable extends Migration
{
    public function __construct()
    {
        Type::hasType('enum') ?: Type::addType('enum', StringType::class);
        Type::hasType('corporation_title_roles_type') ?: Type::addType('corporation_title_roles_type', StringType::class);

        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('corporation_title_roles_type', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('corporation_title_roles')
            ->whereNotIn('corporation_id', DB::table('corporation_titles')->select('corporation_id')->distinct())
            ->delete();

        // create temporary column
        Schema::table('corporation_title_roles', function (Blueprint $table) {
            $table->integer('title_id_temporary')->unsigned()->nullable();
        });

        // migrate to temporary column
        DB::table('corporation_titles')
            ->get()
            ->each(function ($title) {
                DB::table('corporation_title_roles')
                    ->where('corporation_id', $title->corporation_id)
                    ->where('title_id', $title->title_id)
                    ->update(['title_id_temporary' => $title->id]);
            });

        // delete invalid corporation_title_roles since they always must have a title ID
        DB::table('corporation_title_roles')->where('title_id_temporary', null)->delete();

        // change the table to the format we want
        Schema::table('corporation_title_roles', function (Blueprint $table) {
            $table->dropPrimary('corporation_title_roles_primary_key');
            $table->dropColumn('corporation_id');
            $table->integer('title_id')->unsigned()->change();
        });

        // move the data from the temporary column to the correct column
        DB::table('corporation_title_roles')->update(['title_id' => DB::raw('`title_id_temporary`')]);

        // final touches plus remove the temporary column
        Schema::table('corporation_title_roles', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
            $table->dropColumn('title_id_temporary');

            $table->unique(['title_id', 'type', 'role']);

            $table->foreign('title_id')
                ->references('id')
                ->on('corporation_titles')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corporation_title_roles', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique(['title_id', 'type', 'role']);

            $table->bigInteger('corporation_id')->nullable()->first();

            $table->dropForeign('corporation_title_roles_title_id_foreign');

            // add temporary column for migration
            $table->integer('title_id_temporary')->unsigned()->nullable();
        });

        // restore corporation id field
        DB::table('corporation_titles')
            ->get()
            ->each(function ($title) {
                DB::table('corporation_title_roles')
                    ->whereNull('corporation_id')
                    ->where('title_id', $title->id)
                    ->update([
                        'corporation_id' => $title->corporation_id,
                    ]);
            });

        // restore title id field
        DB::table('corporation_titles')
            ->get()
            ->each(function ($title) {
                DB::table('corporation_title_roles')
                    ->where('title_id', $title->id)
                    ->where('corporation_id', $title->corporation_id)
                    ->update([
                        'title_id_temporary' => $title->title_id,
                    ]);
            });

        // delete invalid corporation_title_roles since they always must have a title ID
        DB::table('corporation_title_roles')->where('title_id_temporary', null)->delete();

        DB::table('corporation_title_roles')->update(['title_id' => DB::raw('`title_id_temporary`')]);

        Schema::table('corporation_title_roles', function (Blueprint $table) {
            $table->bigInteger('corporation_id')->nullable(false)->change();
            $table->primary(['corporation_id', 'title_id', 'type', 'role']);
            $table->dropColumn('title_id_temporary');
        });
    }
}
