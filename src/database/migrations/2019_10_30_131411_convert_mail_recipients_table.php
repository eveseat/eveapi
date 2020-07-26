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
 * Class ConvertMailRecipientsTable.
 */
class ConvertMailRecipientsTable extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('mig_mail_recipients', function (Blueprint $table) {
            $table->index(['mail_id', 'recipient_id']);
        });

        // remove duplicate entries with sames labels
        DB::statement('DELETE a FROM mig_mail_recipients a INNER JOIN mig_mail_recipients b WHERE a.id < b.id AND a.mail_id = b.mail_id AND a.recipient_id = b.recipient_id AND a.labels = b.labels');

        // remove duplicate entries without labels
        DB::statement('DELETE a FROM mig_mail_recipients a INNER JOIN mig_mail_recipients b WHERE a.mail_id = b.mail_id AND a.recipient_id = b.recipient_id AND a.labels = "[]" AND b.labels <> "[]"');

        // remove all remaining duplicate entries
        DB::statement('DELETE a FROM mig_mail_recipients a INNER JOIN mig_mail_recipients b WHERE a.id > b.id AND a.mail_id = b.mail_id AND a.recipient_id = b.recipient_id');

        // remove all entries already stored into mail_recipients
        DB::statement('DELETE a FROM mail_recipients a INNER JOIN mig_mail_recipients b WHERE a.mail_id = b.mail_id AND a.recipient_id = b.recipient_id');

        // seed mail_recipients with updated labels and read flag
        DB::table('mail_recipients')
            ->insertUsing(
                ['mail_id', 'recipient_id', 'recipient_type', 'is_read', 'labels'],
                DB::table('mig_mail_recipients')
                ->select('mail_id', 'recipient_id', DB::raw('"character"'), 'is_read', 'labels')
            );

        Schema::dropIfExists('mig_mail_recipients');

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {

    }
}
