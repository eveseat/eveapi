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

/**
 * Trait HasCompositePrimaryKey.
 * @package Seat\Eveapi\Traits
 */
trait HasCompositePrimaryKey
{
    /**
     * @return bool
     */
    public function getIncrementing()
    {

        return false;
    }

    /**
     * Sadly, composite primary keys in Eloquent does not seem to
     * be a *thing*. This override allows for things like firstOrUpdate()
     * to work. However, many other eloquent static methods don't work with
     * composite keys. ¯\_(ツ)_/¯.
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
}
