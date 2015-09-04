<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EveApiKey
 * @package Seat\Eveapi\Models
 */
class EveApiKey extends Model
{

    /**
     * @var string
     */
    protected $primaryKey = 'key_id';

    /**
     * @var array
     */
    protected $fillable = ['key_id', 'v_code', 'user_id', 'enabled', 'last_error'];
}
