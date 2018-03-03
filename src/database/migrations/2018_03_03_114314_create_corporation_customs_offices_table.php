<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationCustomsOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_customs_offices', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('office_id');
            $table->integer('system_id');
            $table->integer('reinforce_exit_start');
            $table->integer('reinforce_exit_end');
            $table->float('corporation_tax_rate')->nullable();
            $table->boolean('allow_alliance_access');
            $table->float('alliance_tax_rate')->nullable();
            $table->boolean('allow_access_with_standings');
            $table->enum('standing_level', ['bad', 'excellent', 'good', 'neutral', 'terrible'])->nullable();
            $table->float('excellent_standing_tax_rate')->nullable();
            $table->float('good_standing_tax_rate')->nullable();
            $table->float('neutral_standing_tax_rate')->nullable();
            $table->float('bad_standing_tax_rate')->nullable();
            $table->float('terrible_standing_tax_rate')->nullable();
            $table->bigInteger('location_id')->nullable();
            $table->double('x')->nullable();
            $table->double('y')->nullable();
            $table->double('z')->nullable();

            $table->primary(['corporation_id', 'office_id']);
            $table->index('corporation_id');
            $table->index('system_id');

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

        Schema::dropIfExists('corporation_customs_offices');
    }
}
