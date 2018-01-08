<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_roles', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->string('role');
            $table->enum('scope',
                ['roles', 'roles_at_hq', 'roles_at_base', 'roles_at_other']);

            $table->index('character_id');
            $table->index('role');

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

        Schema::dropIfExists('character_roles');
    }
}
