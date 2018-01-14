<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 12/01/2018
 * Time: 21:26
 */

namespace Seat\Eveapi\Models\PlanetaryInteraction;


use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

class CharacterPlanetPin extends Model
{

    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $primaryKey = ['character_id', 'planet_id', 'pin_id'];

    /**
     * Return the planet installation to which the pin in attached
     *
     * @return \Seat\Eveapi\Traits\SurrogateBelongsTo
     * @throws \Seat\Eveapi\Exception\SurrogateKeyException
     */
    public function planet()
    {
        return $this->belongsTo(
            CharacterPlanet::class,
            ['character_id', 'planet_id'],
            ['character_id', 'planet_id']);
    }

    public function factory()
    {
        return $this->hasOne(
            CharacterPlanetFactory::class,
            ['character_id', 'planet_id', 'pin_id'],
            ['character_id', 'planet_id', 'pin_id']
        );
    }

}
