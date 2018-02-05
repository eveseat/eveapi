<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 04/02/2018
 * Time: 14:15
 */

namespace Seat\Eveapi\Models\Sde;


use Illuminate\Database\Eloquent\Model;

// TODO : build a readonly Model
class StaStation extends Model
{

    public $incrementing = false;

    protected $table = 'staStations';

    protected $primaryKey = 'stationID';

    /**
     * @return bool
     */
    public function forceDelete()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return false;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        return false;
    }

}
