<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_contacts', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('contact_id');
            $table->float('standing');
            $table->enum('contact_type',
                ['character', 'corporation', 'alliance', 'faction']);
            $table->boolean('is_watched')->nullable();
            $table->boolean('is_blocked')->nullable();
            $table->bigInteger('label_id')->nullable();

            $table->primary(['character_id', 'contact_id']);

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

        Schema::dropIfExists('character_contacts');
    }
}
