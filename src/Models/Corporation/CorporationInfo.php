<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Models\Corporation;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Contacts\CorporationContact;
use Seat\Eveapi\Models\Contacts\CorporationLabel;
use Seat\Eveapi\Models\Contracts\CorporationContract;
use Seat\Eveapi\Models\Industry\CorporationIndustryJob;
use Seat\Eveapi\Models\Market\CorporationOrder;
use Seat\Eveapi\Models\PlanetaryInteraction\CorporationCustomsOffice;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Wallet\CorporationWalletBalance;
use Seat\Eveapi\Models\Wallet\CorporationWalletJournal;
use Seat\Eveapi\Models\Wallet\CorporationWalletTransaction;

/**
 * Class CorporationInfo.
 * @package Seat\Eveapi\Models\Corporation
 *
 * @OA\Schema(
 *      description="Corporation Sheet",
 *      title="CorporationInfo",
 *      type="object"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="name",
 *     description="The name of the corporation"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="ticker",
 *     description="The corporation ticker name"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="member_count",
 *     description="The member amount of the corporation"
 * )
 *
 * @OA\Property(
 *     property="ceo",
 *     description="The character ID of the corporation CEO",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     property="alliance",
 *     description="The alliance of the corporation if any",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="description",
 *     description="The corporation description"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="float",
 *     property="tax_rate",
 *     description="The corporation tax rate"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="date_founded",
 *     description="The corporation creation date"
 * )
 *
 * @OA\Property(
 *     property="creator",
 *     description="The corporation founder character",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="uri",
 *     property="url",
 *     description="The corporation homepage link"
 * )
 *
 * @OA\Property(
 *     property="faction",
 *     description="The corporation faction if any",
 *     ref="#/components/schemas/UniverseName"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="home_station_id",
 *     description="The home station where the corporation has its HQ"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="shares",
 *     description="The shares attached to the corporation"
 * )
 */
class CorporationInfo extends Model
{
    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'corporation_id';

    /**
     * @param $value
     */
    public function setDateFoundedAttribute($value)
    {
        $this->attributes['date_founded'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNpc($query)
    {
        return $query->whereBetween('corporation_id', [1000000, 1999999]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePlayer($query)
    {
        return $query->whereNotBetween('corporation_id', [1000000, 1999999]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function alliance()
    {
        return $this->hasOne(Alliance::class, 'alliance_id', 'alliance_id')
            ->withDefault([
                'name'        => '',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function alliance_history()
    {

        return $this->hasMany(CorporationAllianceHistory::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {

        return $this->hasMany(CorporationAsset::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blueprints()
    {

        return $this->hasMany(CorporationBlueprint::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {

        return $this->hasMany(CorporationContact::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contact_labels()
    {

        return $this->hasMany(CorporationLabel::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function container_logs()
    {

        return $this->hasMany(CorporationContainerLog::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts()
    {

        return $this->hasMany(CorporationContract::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function characters()
    {
        return $this->hasManyThrough(CharacterInfo::class, CharacterAffiliation::class,
            'corporation_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ceo()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'ceo_id')
            ->withDefault([
                'category'  => 'character',
                'name'      => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function creator()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'creator_id')
            ->withDefault([
                'category' => 'character',
                'name'     => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function divisions()
    {

        return $this->hasMany(CorporationDivision::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function facilities()
    {

        return $this->hasMany(CorporationFacility::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function faction()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'faction_id')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function home_station()
    {

        return $this->belongsTo(UniverseStation::class,
            'home_station_id', 'station_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function industry_jobs()
    {

        return $this->hasMany(CorporationIndustryJob::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issued_medals()
    {

        return $this->hasMany(CorporationIssuedMedal::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medals()
    {

        return $this->hasMany(CorporationMedal::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function members()
    {

        return $this->hasMany(CorporationMember::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function member_limit()
    {

        return $this->hasOne(CorporationMemberLimits::class,
            'corporation_id', 'corporation_id')
            ->withDefault([
                'limit' => 0,
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function member_titles()
    {

        return $this->hasMany(CorporationMemberTitle::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function member_tracking()
    {

        return $this->hasMany(CorporationMemberTracking::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {

        return $this->hasMany(CorporationOrder::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pocos()
    {

        return $this->hasMany(CorporationCustomsOffice::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roles()
    {

        return $this->hasMany(CorporationRole::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function role_history()
    {

        return $this->hasMany(CorporationRoleHistory::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shareholders()
    {

        return $this->hasMany(CorporationShareholder::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function standings()
    {

        return $this->hasMany(CorporationStanding::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function titles()
    {

        return $this->hasMany(CorporationTitle::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function title_roles()
    {

        return $this->hasMany(CorporationTitleRole::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallet_balances()
    {

        return $this->hasMany(CorporationWalletBalance::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallet_journal()
    {

        return $this->hasMany(CorporationWalletJournal::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallet_transactions()
    {

        return $this->hasMany(CorporationWalletTransaction::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function structures()
    {
        return $this->hasMany(CorporationStructure::class, 'corporation_id', 'corporation_id');
    }
}
