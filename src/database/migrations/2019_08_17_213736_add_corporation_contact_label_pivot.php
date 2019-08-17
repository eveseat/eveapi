<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Models\Contacts\CorporationContact;
use Seat\Eveapi\Models\Contacts\CorporationLabel;

/**
 * Class AddCorporationContactLabelPivot.
 */
class AddCorporationContactLabelPivot extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('corporation_contact_corporation_label', function (Blueprint $table) {
            $table->bigInteger('corporation_contact_id');
            $table->integer('corporation_label_id');
            $table->primary(['corporation_contact_id', 'corporation_label_id'], 'corporation_contact_label_pk');
        });

        // collecting all contact with a label set
        $contacts = CorporationContact::whereNotNull('label_ids')->get();

        // seeding pivot table
        foreach ($contacts as $contact) {
            $contact->labels()
                ->sync(CorporationLabel::where('corporation_id', $contact->corporation_id)
                    ->whereIn('label_id', $contact->label_ids)
                    ->select('id')
                    ->get());
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::drop('corporation_contact_corporation_label');

        Schema::enableForeignKeyConstraints();
    }
}
