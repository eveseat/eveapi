<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 12/01/2018
 * Time: 21:34
 */

namespace Seat\Eveapi\Models\PlanetaryInteraction;


use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

class CharacterPlanetRoute extends Model
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
    protected $primaryKey = ['character_id', 'planet_id', 'route_id'];

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

    /**
     * Return the pin from which the route is starting
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function source()
    {
        return $this->hasOne(
            CharacterPlanetPin::class,
            ['character_id', 'planet_id', 'pin_id'],
            ['character_id', 'planet_id', 'source_pin_id']);
    }

    /**
     * Return the pin to which the route is going
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function destination()
    {
        return $this->hasOne(
            CharacterPlanetPin::class,
            ['character_id', 'planet_id', 'pin_id'],
            ['character_id', 'planet_id', 'destination_pin_id']);
    }
}
