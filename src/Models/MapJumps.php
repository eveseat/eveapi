<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MapJumps
 * @package App
 */
class MapJumps extends Model
{

    /**
     * @var string
     */
    protected $primaryKey = 'solarSystemID';

    /**
     * @var array
     */
    protected $fillable = ['solarSystemID', 'shipJumps'];
}
