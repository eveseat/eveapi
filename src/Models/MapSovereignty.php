<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MapSovereignty
 * @package Seat\Eveapi\Models
 */
class MapSovereignty extends Model
{

    /**
     * @var string
     */
    protected $primaryKey = 'solarSystemID';

    /**
     * @var array
     */
    protected $fillable = [
        'solarSystemID', 'allianceID', 'factionID', 'solarSystemName', 'corporationID'];
}
