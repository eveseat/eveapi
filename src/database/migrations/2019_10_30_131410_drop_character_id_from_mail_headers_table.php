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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class DropCharacterIdFromMailHeadersTable.
 */
class DropCharacterIdFromMailHeadersTable extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        // spawn migration tables which will be used to re-seed tables properly
        Schema::create('mig_mail_headers', function (Blueprint $table) {
            $table->bigInteger('character_id');
            $table->bigInteger('mail_id');
            $table->string('subject');
            $table->bigInteger('from');
            $table->dateTime('timestamp');
        });

        Schema::create('mig_mail_recipients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mail_id');
            $table->bigInteger('recipient_id');
            $table->boolean('is_read');
            $table->json('labels');
        });

        // collect all mail headers and seed migration table
        DB::table('mig_mail_headers')
            ->insertUsing(
                ['character_id', 'mail_id', 'subject', 'from', 'timestamp'],
                DB::table('mail_headers')
                    ->select('character_id', 'mail_id', 'subject', 'from', 'timestamp')
            );

        // collect all mail <-> character entries and seed migration table
        DB::table('mig_mail_recipients')
            ->insertUsing(
                ['mail_id', 'recipient_id', 'is_read', 'labels'],
                DB::table('mail_headers')
                    ->select('mail_id', 'character_id', 'is_read', 'labels')
                    ->whereColumn('character_id', '<>', 'from')
                    ->whereNotIn(DB::raw("CONCAT(mail_id, '-', character_id)"),
                        DB::table('mail_recipients')
                            ->selectRaw("CONCAT(mail_id, '-', recipient_id)"))
            );

        // emptying mail headers table
        DB::table('mail_headers')->truncate();

        // update mail headers table
        Schema::table('mail_headers', function (Blueprint $table) {
            $table->dropColumn('character_id');
            $table->dropColumn('labels');
            $table->dropColumn('is_read');
            $table->dropIndex(['timestamp']);
            $table->primary('mail_id');
        });

        // seed mail headers table using migration table
        DB::table('mail_headers')
            ->insertUsing(
                ['mail_id', 'subject', 'from', 'timestamp', 'created_at', 'updated_at'],
                DB::table('mig_mail_headers')
                    ->select('mail_id', DB::raw('TRIM(REPLACE(subject, \'	\', \' \'))'), 'from', 'timestamp', DB::raw('now()'), DB::raw('now()'))
                    ->distinct()
            );

        // update mail recipients table
        Schema::table('mail_recipients', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('recipient_type');
            $table->json('labels')->nullable()->after('is_read');
        });

        Schema::dropIfExists('mig_mail_headers');
    }

    public function down()
    {

    }
}
