<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EveAllianceList
 * @package Seat\Eveapi\Models
 */
class EveAllianceList extends Model
{

    /**
     * @var string
     */
    protected $primaryKey = 'allianceID';

    /**
     * @var array
     */
    protected $fillable = [
        'allianceID', 'name', 'shortName', 'executorCorpID', 'memberCount', 'startDate'];

    /**
     * Returns the member corporations for this alliance
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function members()
    {

        return $this->hasMany(
            'Seat\Eveapi\Models\EveAllianceListMemberCorporations', 'allianceID', 'allianceID');
    }
}
