<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Support\Str;
use Seat\Eveapi\Exception\SurrogateKeyException;


/**
 * Trait HasCompositePrimaryKey
 * @package Seat\Eveapi\Traits
 */
trait HasCompositePrimaryKey
{

	use HasRelationships {
		HasRelationships::belongsTo as parentBelongsTo;
	}

    /**
     * @return bool
     */
    public function getIncrementing()
    {

        return false;
    }

    /**
     * Sadly, composite primary keys in Eloquent does not seem to
     * be a *thing*. This override allowes for things like firstOrUpdate()
     * to work. However, many other eloquent static methods dont work with
     * composite keys. ¯\_(ツ)_/¯
     *
     * Monkey patch refs:
     *  https://github.com/laravel/framework/issues/5517#issuecomment-113655441
     *  https://github.com/laravel/framework/issues/5355#issuecomment-161376267
     *  https://github.com/warlof/eveseat-mining-ledger/blob/a03e15354d00567db46ec883a1e803824350c26b/src/Models/Character/MiningJournal.php#L46-L66
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {

        if (is_array($this->getKeyName())) {
            foreach ((array) $this->getKeyName() as $keyField) {
                $query->where($keyField, '=', $this->original[$keyField]);
            }

            return $query;
        }

        return parent::setKeysForSaveQuery($query);
    }

	/**
	 * @param array $ids
	 * @param array $columns
	 *
	 * @throws SurrogateKeyException
	 */
    public static function find(array $ids, $columns = ['*'])
    {
    	$object = (new static);
    	$query = $object->newQuery();

    	if (!is_array($object->getKeyName()))
    		throw new SurrogateKeyException('The model does not have a surrogate key !');

    	foreach ($object->getKeyName() as $key => $column) {
		    $query->where($column, $ids[$key]);
	    }

	    return $object->first($columns);
    }

	/**
	 * @param $related
	 * @param null $foreignKey
	 * @param null $otherKey
	 * @param null $relation
	 *
	 * @return SurrogateBelongsTo
	 * @throws SurrogateKeyException
	 */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
	    // If no relation name was given, we will use this debug backtrace to extract
	    // the calling method's name and use that as the relationship name as most
	    // of the time this will be what we desire to use for the relationships.
	    if (is_null($relation)) {
		    $relation = $this->guessBelongsToRelation();
	    }

	    $instance = $this->newRelatedInstance($related);

	    // If no foreign key was supplied, we can use a backtrace to guess the proper
	    // foreign key name by using the name of the relationship function, which
	    // when combined with an "_id" should conventionally match the columns.
	    if (is_null($foreignKey)) {
		    $foreignKey = Str::snake($relation) . '_' . $instance->getKeyName();
	    }

	    $ownerKey = $ownerKey ?: $instance->getKeyName();

	    return new SurrogateBelongsTo($instance->newQuery(), $this, $foreignKey, $ownerKey, $relation);
    }
}
