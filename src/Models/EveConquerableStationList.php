<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EveConquerableStationList
 * @package Seat\Eveapi\Models
 */
class EveConquerableStationList extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'stationID', 'stationName', 'stationTypeID', 'solarSystemID', 'corporationID', 'corporationName'];
}
