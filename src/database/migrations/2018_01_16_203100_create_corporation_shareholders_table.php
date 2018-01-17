<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationShareholdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_shareholders', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->enum('shareholder_type', ['character', 'corporation']);
            $table->bigInteger('shareholder_id');
            $table->bigInteger('share_count');

            $table->primary(['corporation_id', 'shareholder_type', 'shareholder_id'],
                'corporation_shareholders_primary_key');
            $table->index('corporation_id');
            $table->index(['shareholder_type', 'shareholder_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('corporation_shareholders');
    }
}
