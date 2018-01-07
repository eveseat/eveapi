<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCharacterInfoTableAddSkillpointInfo extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('character_infos', function(Blueprint $table){
            $table->integer('total_sp')
                  ->default(0)
                  ->after('faction_id');
            $table->integer('unallocated_sp')
                  ->default(0)
                  ->after('total_sp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('character_infos', function(Blueprint $table){
            $table->dropColumn('total_sp');
            $table->dropColumn('unallocated_sp');
        });
    }

}
