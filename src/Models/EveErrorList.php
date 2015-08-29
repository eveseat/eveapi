<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EveErrorList
 * @package App
 */
class EveErrorList extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['errorCode', 'errorText'];
}
