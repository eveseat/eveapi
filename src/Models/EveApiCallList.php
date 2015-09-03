<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EveApiCallList
 * @package Seat\Eveapi\Models
 */
class EveApiCallList extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['type', 'name', 'accessMask', 'description'];
}
