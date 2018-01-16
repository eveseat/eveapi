<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_contacts', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('contact_id');
            $table->float('standing');
            $table->enum('contact_type',
                ['character', 'corporation', 'alliance', 'faction']);
            $table->boolean('is_watched')->nullable();
            $table->bigInteger('label_id')->nullable();

            $table->primary(['corporation_id', 'contact_id']);
            $table->index('corporation_id');

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

        Schema::dropIfExists('corporation_contacts');
    }
}
