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
 * Class AddUniqueIndexToMailRecipientsTable.
 */
class AddUniqueIndexToMailRecipientsTable extends Migration
{
    public function up()
    {
        Schema::create('mig_mail_recipients', function (Blueprint $table) {
            $table->bigInteger('mail_id');
            $table->bigInteger('recipient_id');
            $table->enum('recipient_type', [
                'alliance', 'character', 'corporation', 'mailing_list',
            ]);
        });

        // remove orphan recipients
        DB::table('mail_recipients')
            ->leftJoin('mail_headers', 'mail_recipients.mail_id', '=', 'mail_headers.mail_id')
            ->whereNull('mail_headers.mail_id')
            ->delete();

        DB::table('mig_mail_recipients')
            ->insertUsing(
                ['mail_id', 'recipient_id', 'recipient_type'],
                DB::table('mail_recipients')
                    ->select('mail_id', 'recipient_id', 'recipient_type')
                    ->groupBy('mail_id', 'recipient_id', 'recipient_type')
            );

        DB::table('mail_recipients')->truncate();

        Schema::table('mail_recipients', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->unique(['mail_id', 'recipient_id', 'recipient_type']);
        });

        DB::table('mail_recipients')
            ->insertUsing(
                ['mail_id', 'recipient_id', 'recipient_type'],
                DB::table('mig_mail_recipients')
            );

        Schema::dropIfExists('mig_mail_recipients');
    }

    public function down()
    {
        Schema::table('mail_recipients', function (Blueprint $table) {
            $table->dropUnique(['mail_id', 'recipient_id', 'recipient_type']);
            $table->timestamps();
        });
    }
}
