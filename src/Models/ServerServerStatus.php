<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

class ServerServerStatus extends Model
{

    protected $fillable = ['currentTime', 'serverOpen', 'onlinePlayers'];
}
