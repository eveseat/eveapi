<?php


namespace Seat\Eveapi\Models\Fittings;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Insurance.
 *
 * @package Seat\Eveapi\Models\Fittings
 */
class Insurance extends Model
{
    /**
     * @var string
     */
    protected $table = 'insurances';

    /**
     * @var bool
     */
    protected static $unguarded = true;
}
