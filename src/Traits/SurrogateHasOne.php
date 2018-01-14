<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 12/01/2018
 * Time: 23:12
 */

namespace Seat\Eveapi\Traits;


use Illuminate\Database\Eloquent\Relations\HasOne;
use Seat\Eveapi\Exception\SurrogateKeyException;

class SurrogateHasOne extends HasOne
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
				$this->query->where( $table . '.' . $column, '=', $this->getParentSurrogateKey($this->localKey[$key]));

		}

	}

	public function getParentSurrogateKey($keyName)
	{
		return $this->parent->getAttribute($keyName);
	}

}
