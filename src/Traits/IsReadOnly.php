<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

use Seat\Eveapi\Exception\ReadOnlyModelException;

/**
 * Trait IsReadOnly.
 *
 * @package Seat\Eveapi\Traits
 */
trait IsReadOnly
{
    /**
     * @var bool
     */
    private static bool $bypass_read_only = false;

    /**
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|$this
     *
     * @throws \Seat\Eveapi\Exception\ReadOnlyModelException
     */
    public static function create(array $attributes = [])
    {
        if (self::$bypass_read_only)
            return parent::create($attributes);

        throw new ReadOnlyModelException;
    }

    /**
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Seat\Eveapi\Exception\ReadOnlyModelException
     */
    public static function firstOrCreate(array $attributes = [], array $values = [])
    {
        if (self::$bypass_read_only)
            return parent::firstOrCreate($attributes, $values);

        throw new ReadOnlyModelException;
    }

    /**
     * @param  array  $options
     *
     * @eturn bool
     *
     * @throws \Seat\Eveapi\Exception\ReadOnlyModelException
     */
    public function save(array $options = [])
    {
        if (self::$bypass_read_only)
            return parent::save($options);

        throw new ReadOnlyModelException;
    }

    /**
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     *
     * @throws \Seat\Eveapi\Exception\ReadOnlyModelException
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (self::$bypass_read_only)
            return parent::update($attributes, $options);

        throw new ReadOnlyModelException;
    }

    /**
     * @return bool|null
     *
     * @throws \Seat\Eveapi\Exception\ReadOnlyModelException
     */
    public function delete()
    {
        if (self::$bypass_read_only)
            return parent::delete();

        throw new ReadOnlyModelException;
    }

    /**
     * @return bool|null
     *
     * @throws \Seat\Eveapi\Exception\ReadOnlyModelException
     */
    public function forceDelete()
    {
        if (self::$bypass_read_only)
            return parent::forceDelete();

        throw new ReadOnlyModelException;
    }

    /**
     * @param  bool  $new_value
     * @return $this
     */
    public function bypassReadOnly(bool $new_value = true)
    {
        self::$bypass_read_only = true;

        return $this;
    }
}
