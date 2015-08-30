<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EveErrorList
 * @package Seat\Eveapi\Models
 */
class EveErrorList extends Model
{

    /**
     * @var string
     */
    protected $primaryKey = 'errorCode';

    /**
     * @var array
     */
    protected $fillable = ['errorCode', 'errorText'];
}
