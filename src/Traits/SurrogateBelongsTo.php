<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 12/01/2018
 * Time: 23:12
 */

namespace Seat\Eveapi\Traits;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Seat\Eveapi\Exception\SurrogateKeyException;

class SurrogateBelongsTo extends BelongsTo
{

	/**
	 * @var array
	 */
	protected $foreignKey = [];

	/**
	 * @return array
	 */
	public function getForeignKey() {
		return $this->foreignKey;
	}

	/**
	 * @throws SurrogateKeyException
	 */
	public function addConstraints() {

		if (static::$constraints) {

			$table = $this->related->getTable();

			if (!is_array($this->getForeignKey()))
				throw new SurrogateKeyException('The relation does not refer to a surrogate key !');

			foreach ($this->foreignKey as $key => $column)
				$this->query->where($table . '.' . $this->ownerKey[$key], '=', $this->parent->{$column});

		}

	}

}
