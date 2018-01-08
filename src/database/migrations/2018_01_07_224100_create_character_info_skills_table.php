<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterInfoSkillsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('character_info_skills', function(Blueprint $table){

        	$table->bigInteger('character_id');
            $table->integer('total_sp')
                  ->default(0);
            $table->integer('unallocated_sp')
                  ->default(0);

	        $table->primary('character_id');

	        $table->timestamps();

	        $table->foreign('character_id')
	              ->references('character_id')
	              ->on('character_infos')
	              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('character_info_skills');
    }

}
