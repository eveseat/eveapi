<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_structures', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('structure_id');
            $table->integer('type_id');
            $table->integer('system_id');
            $table->integer('profile_id');
            $table->dateTime('fuel_expires')->nullable();
            $table->dateTime('state_timer_start')->nullable();
            $table->dateTime('state_timer_end')->nullable();
            $table->dateTime('unanchors_at')->nullable();
            $table->enum('state', [
                'anchor_vulnerable', 'anchoring', 'armor_reinforce', 'armor_vulnerable',
                'fitting_invulnerable', 'hull_reinforce', 'hull_vulnerable', 'online_deprecated',
                'onlining_vulnerable', 'shield_vulnerable', 'unanchored', 'unknown',
            ]);
            $table->integer('reinforce_weekday');
            $table->integer('reinforce_hour');
            $table->integer('next_reinforce_weekday')->nullable();
            $table->integer('next_reinforce_hour')->nullable();
            $table->dateTime('next_reinforce_apply')->nullable();

            $table->primary(['corporation_id', 'structure_id'], 'corporation_structures_primary_key');
            $table->index('corporation_id');
            $table->index('structure_id');
            $table->index('system_id');
            $table->index('profile_id');
            $table->index('type_id');

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

        Schema::dropIfExists('corporation_structures');
    }
}
