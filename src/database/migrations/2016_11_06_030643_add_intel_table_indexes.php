<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddIntelTableIndexes
 */
class AddIntelTableIndexes extends Migration
{

    /**
     * Tablenames and columns that should be altered by this
     * migration.
     *
     * @var array
     */
    protected $table_columns = [
        'character_wallet_journals'       => ['ownerID1', 'ownerID2'],
        'character_wallet_transactions'   => ['clientID'],
        'character_contracts'             => ['assigneeID', 'acceptorID'],
        'corporation_wallet_journals'     => ['ownerID1', 'ownerID2'],
        'corporation_wallet_transactions' => ['clientID'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        foreach ($this->table_columns as $table_name => $columns) {

            Schema::table($table_name, function ($table) use ($columns) {

                foreach ($columns as $column)
                    $table->index([$column]);
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        foreach ($this->table_columns as $table_name => $columns) {

            Schema::table($table_name, function ($table) use ($columns) {

                foreach ($columns as $column)
                    $table->dropIndex([$column]);
            });
        }
    }
}
