<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ServerServerStatus
 * @package Seat\Eveapi\Models
 */
class ServerServerStatus extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['currentTime', 'serverOpen', 'onlinePlayers'];
}
