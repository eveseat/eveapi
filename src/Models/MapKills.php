<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MapKills
 * @package Seat\Eveapi\Models
 */
class MapKills extends Model
{

    /**
     * @var string
     */
    protected $primaryKey = 'solarSystemID';

    /**
     * @var array
     */
    protected $fillable = ['solarSystemID', 'shipKills', 'factionKills', 'podKills'];
}
