<?php


namespace Seat\Eveapi\Models\Sde;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\IsReadOnly;

/**
 * Class RamActivity.
 *
 * @package Seat\Eveapi\Models\Sde
 */
class RamActivity extends Model
{
    use IsReadOnly;

    protected $table = 'ramActivities';

    protected $primaryKey = 'activityID';
}
