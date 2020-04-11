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
use Illuminate\Support\Facades\Schema;
use Seat\Eveapi\Models\Contacts\CharacterContact;
use Seat\Eveapi\Models\Contacts\CharacterLabel;

/**
 * Class AddCharacterContactLabelPivot.
 */
class AddCharacterContactLabelPivot extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('character_contact_character_label', function (Blueprint $table) {
            $table->bigInteger('character_contact_id');
            $table->integer('character_label_id');
            $table->primary(['character_contact_id', 'character_label_id'], 'character_contact_label_pk');
        });

        // collecting all contact with a label set
        $contacts = CharacterContact::whereNotNull('label_ids')->get();

        // seeding pivot table
        foreach ($contacts as $contact) {
            $contact->labels()
                ->sync(CharacterLabel::where('character_id', $contact->character_id)
                    ->whereIn('label_id', $contact->label_ids)
                    ->select('id')
                    ->get());
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::drop('character_contact_character_label');

        Schema::enableForeignKeyConstraints();
    }
}
