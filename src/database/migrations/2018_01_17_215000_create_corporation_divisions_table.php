<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationDivisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_divisions', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->enum('type', ['hangar', 'wallet']);
            $table->integer('division');
            $table->string('name')->nullable();

            $table->primary(['corporation_id', 'type', 'division'], 'corporation_divisions_primary_key');
            $table->index('corporation_id');
            $table->index(['type', 'division']);

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

        Schema::dropIfExists('corporation_divisions');
    }
}
