<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 12/01/2018
 * Time: 21:34
 */

namespace Seat\Eveapi\Models\PlanetaryInteraction;


use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

class CharacterPlanetHead extends Model
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
	protected $primaryKey = ['character_id', 'planet_id', 'extractor_id', 'head_id'];

	/**
	 * Return the planet installation to which the head is attached
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
	 * Return the extractor to which the head is attached
	 *
	 * @return \Seat\Eveapi\Traits\SurrogateBelongsTo
	 * @throws \Seat\Eveapi\Exception\SurrogateKeyException
	 */
	public function extractor()
	{
		return $this->belongsTo(
			CharacterPlanetExtractor::class,
			['character_id', 'planet_id', 'extractor_id'],
			['character_id', 'planet_id', 'pin_id']
		);
	}

	/**
	 * @return \Seat\Eveapi\Traits\SurrogateBelongsTo
	 * @throws \Seat\Eveapi\Exception\SurrogateKeyException
	 */
	public function pin()
	{
		return $this->belongsTo(
			CharacterPlanetPin::class,
			['character_id', 'planet_id', 'extractor_id'],
			['character_id', 'planet_id', 'pin_id']);
	}
}
