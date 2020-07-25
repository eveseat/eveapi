<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020  Leon Jacobs
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
 * Class RollbackEveApi3021.
 */
class RollbackEveApi3021 extends Migration
{
    const EVEAPI_3_0_21_MIGRATIONS = [
        '2019_08_25_090128_remove_corporation_standings_surrogate_key',
        '2019_08_24_234651_remove_corporation_bookmark_folders_surrogate_key',
        '2019_08_24_234635_remove_corporation_bookmarks_surrogate_key',
        '2019_08_24_193243_remove_character_bookmark_folders_surrogate_key',
        '2019_08_24_192640_remove_character_bookmarks_surrogate_key',
        '2019_08_22_202215_create_insurances_table',
        '2019_08_17_213736_add_corporation_contact_label_pivot',
        '2019_08_17_213640_add_character_contact_label_pivot',
        '2019_08_17_213047_remove_corporation_contact_labels_surrogate_key',
        '2019_08_17_212554_remove_corporation_contacts_surrogate_key',
        '2019_08_17_212459_remove_character_contact_labels_surrogate_key',
        '2019_08_17_212222_remove_character_contacts_surrogate_key',
    ];

    const EVEAPI_4_0_0_MARK = '2019_05_11_164831_add_permission_role_filter';

    public function up()
    {
        // detect if migration is run over a 4.0.0 version
        // do not run this migration if we found a v4.0.0 migration
        if ($this->test_if_migration_exists(self::EVEAPI_4_0_0_MARK))
            return;

        // loop over each 3.0.21 shipped migrations and apply hotfix if required
        foreach (self::EVEAPI_3_0_21_MIGRATIONS as $migration) {
            $this->rollback($migration);
        }
    }

    public function down()
    {

    }

    private function rollback(string $migration)
    {
        // requested migration does not exists, ignore hotfix process
        if (! $this->test_if_migration_exists($migration))
            return;

        // apply hotfix related to this migration
        switch ($migration) {
            case '2019_08_25_090128_remove_corporation_standings_surrogate_key':
                $this->remove_corporation_standings_surrogate_key();
                break;
            case '2019_08_24_234651_remove_corporation_bookmark_folders_surrogate_key':
                $this->remove_corporation_bookmark_folders_surrogate_key();
                break;
            case '2019_08_24_234635_remove_corporation_bookmarks_surrogate_key':
                $this->remove_corporation_bookmarks_surrogate_key();
                break;
            case '2019_08_24_193243_remove_character_bookmark_folders_surrogate_key':
                $this->remove_character_bookmark_folders_surrogate_key();
                break;
            case '2019_08_24_192640_remove_character_bookmarks_surrogate_key':
                $this->remove_character_bookmarks_surrogate_key();
                break;
            case '2019_08_22_202215_create_insurances_table':
                $this->create_insurances_table();
                break;
            case '2019_08_17_213736_add_corporation_contact_label_pivot':
                $this->add_corporation_contact_label_pivot();
                break;
            case '2019_08_17_213640_add_character_contact_label_pivot':
                $this->add_character_contact_label_pivot();
                break;
            case '2019_08_17_213047_remove_corporation_contact_labels_surrogate_key':
                $this->remove_corporation_contact_labels_surrogate_key();
                break;
            case '2019_08_17_212554_remove_corporation_contacts_surrogate_key':
                $this->remove_corporation_contacts_surrogate_key();
                break;
            case '2019_08_17_212459_remove_character_contact_labels_surrogate_key':
                $this->remove_character_contact_labels_surrogate_key();
                break;
            case '2019_08_17_212222_remove_character_contacts_surrogate_key':
                $this->remove_character_contacts_surrogate_key();
                break;
        }

        // mark migration as undone, allowing it to be applied again
        $this->remove_migration_from_history($migration);
    }

    /**
     * @param string $migration
     *
     * @return bool
     */
    private function test_if_migration_exists(string $migration): bool
    {
        return DB::table('migrations')->where('migration', $migration)->exists();
    }

    /**
     * @param string $migration
     */
    private function remove_migration_from_history(string $migration)
    {
        DB::table('migrations')
            ->where('migration', $migration)
            ->delete();
    }

    private function remove_character_contacts_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('character_contacts', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique(['character_id', 'contact_id']);
        });

        Schema::table('character_contacts', function (Blueprint $table) {
            $table->primary(['character_id', 'contact_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    private function remove_character_contact_labels_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('character_labels', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique(['character_id', 'label_id']);
            $table->renameColumn('name', 'label_name');
        });

        Schema::rename('character_labels', 'character_contact_labels');

        Schema::table('character_contact_labels', function (Blueprint $table) {
            $table->primary(['character_id', 'label_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    private function remove_corporation_contacts_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('corporation_contacts', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique(['corporation_id', 'contact_id']);
        });

        Schema::table('corporation_contacts', function (Blueprint $table) {
            $table->primary(['corporation_id', 'contact_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    private function remove_corporation_contact_labels_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('corporation_labels', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique(['corporation_id', 'label_id']);
            $table->renameColumn('name', 'label_name');
        });

        Schema::rename('corporation_labels', 'corporation_contact_labels');

        Schema::table('corporation_contact_labels', function (Blueprint $table) {
            $table->primary(['corporation_id', 'label_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    private function add_character_contact_label_pivot()
    {
        Schema::disableForeignKeyConstraints();

        Schema::drop('character_contact_character_label');

        Schema::enableForeignKeyConstraints();
    }

    private function add_corporation_contact_label_pivot()
    {
        Schema::disableForeignKeyConstraints();

        Schema::drop('corporation_contact_corporation_label');

        Schema::enableForeignKeyConstraints();
    }

    private function create_insurances_table()
    {
        Schema::dropIfExists('insurances');
    }

    private function remove_character_bookmarks_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('character_bookmarks', function (Blueprint $table) {
            $table->dropPrimary();
            $table->primary(['character_id', 'bookmark_id']);
            $table->dropIndex(['character_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    private function remove_character_bookmark_folders_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('character_bookmark_folders', function (Blueprint $table) {
            $table->dropPrimary();
            $table->primary(['character_id', 'folder_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    private function remove_corporation_bookmarks_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('corporation_bookmarks', function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table('corporation_bookmarks', function (Blueprint $table) {
            $table->primary(['corporation_id', 'bookmark_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    private function remove_corporation_bookmark_folders_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('corporation_bookmark_folders', function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table('corporation_bookmark_folders', function (Blueprint $table) {
            $table->primary(['corporation_id', 'folder_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    private function remove_corporation_standings_surrogate_key()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('corporation_standings', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique(['corporation_id', 'from_id']);
        });

        Schema::table('corporation_standings', function (Blueprint $table) {
            $table->primary(['corporation_id', 'from_type', 'from_id']);
        });

        Schema::enableForeignKeyConstraints();
    }
}
