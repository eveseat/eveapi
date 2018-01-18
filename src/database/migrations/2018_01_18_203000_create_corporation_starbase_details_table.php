<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationStarbaseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_starbase_details', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('starbase_id');
            $table->enum('fuel_bay_view', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('fuel_bay_take', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('anchor', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('unanchor', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('online', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->enum('offline', [
                'alliance_member',
                'config_starbase_equipment_role',
                'corporation_member',
                'starbase_fuel_technician_role',
            ]);
            $table->boolean('allow_corporation_members');
            $table->boolean('allow_alliance_members');
            $table->boolean('use_alliance_standings');
            $table->decimal('attack_standing_threshold')->nullable();
            $table->decimal('attack_security_status_threshold')->nullable();
            $table->boolean('attack_if_other_security_status_dropping');
            $table->boolean('attack_if_at_war');

            $table->primary(['corporation_id', 'starbase_id'], 'corporation_starbase_details_primary_key');
            $table->index('corporation_id');
            $table->index('starbase_id');

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

        Schema::dropIfExists('corporation_starbase_details');
    }
}
