<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EveAllianceListMemberCorporations
 * @package Seat\Eveapi\Models
 */
class EveAllianceListMemberCorporations extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['allianceID', 'corporationID', 'startDate'];

    /**
     * Returns the alliance the corporation belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function alliance()
    {

        return $this->belongsTo(
            'Seat\Eveapi\Models\EveAllianceList', 'allianceID', 'allianceID');
    }
}
