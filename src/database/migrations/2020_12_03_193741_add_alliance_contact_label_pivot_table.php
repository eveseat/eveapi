<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllianceContactLabelPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alliance_contact_alliance_label', function (Blueprint $table) {
            $table->unsignedBigInteger('alliance_contact_id');
            $table->unsignedBigInteger('alliance_label_id');
            $table->primary(['alliance_contact_id', 'alliance_label_id'], 'alliance_contact_label_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alliance_contact_alliance_label');
    }
}
