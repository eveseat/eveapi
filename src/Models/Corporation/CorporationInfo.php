<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterLoyaltyPoints;
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
use Seat\Services\Models\ExtensibleModel;
use Seat\Tests\Eveapi\Database\Factories\CorporationInfoFactory;

#[OA\Schema(
    title: 'CorporationInfo',
    description: 'Corporation Sheet',
    properties: [
        new OA\Property(property: 'name', description: 'The name of the corporation', type: 'string'),
        new OA\Property(property: 'ticker', description: 'The corporation ticker', type: 'string'),
        new OA\Property(property: 'member_count', description: 'The member amount of the corporation', type: 'integer'),
        new OA\Property(property: 'ceo', ref: '#/components/schemas/UniverseName', description: 'The corporation CEO'),
        new OA\Property(property: 'alliance', ref: '#/components/schemas/UniverseName', description: 'The alliance of the corporation, if any'),
        new OA\Property(property: 'description', description: 'The corporation description', type: 'string'),
        new OA\Property(property: 'tax_rate', description: 'The corporation tax rate', type: 'number', format: 'float'),
        new OA\Property(property: 'date_founded', description: 'The date/time when this corporation has been created', type: 'string', format: 'date-time'),
        new OA\Property(property: 'creator', ref: '#/components/schemas/UniverseName', description: 'The corporation founder character'),
        new OA\Property(property: 'url', description: 'The corporation homepage link', type: 'string', format: 'uri'),
        new OA\Property(property: 'faction', ref: '#/components/schemas/UniverseName', description: 'The corporation faction, if any'),
        new OA\Property(property: 'home_station_id', description: 'The station into which the corporation has its HQ', type: 'integer', format: 'int64'),
        new OA\Property(property: 'shares', description: 'The shares attached to the corporation', type: 'number', format: 'double'),
    ],
    type: 'object'
)]
class CorporationInfo extends ExtensibleModel
{
    use HasFactory;

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
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory(): Factory
    {
        return CorporationInfoFactory::new();
    }

    /**
     * @param  $value
     */
    public function setDateFoundedAttribute($value)
    {
        $this->attributes['date_founded'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNpc($query)
    {
        return $query->whereBetween('corporation_id', [1000000, 1999999]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePlayer($query)
    {
        return $query->whereNotBetween('corporation_id', [1000000, 1999999]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function loyalty_point_owners()
    {
        return $this->belongsToMany(CharacterInfo::class, 'character_loyalty_points', 'corporation_id', 'character_id')
            ->using(CharacterLoyaltyPoints::class)
            ->withPivot('amount')
            ->as('loyalty_points')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function alliance()
    {
        return $this->hasOne(Alliance::class, 'alliance_id', 'alliance_id')
            ->withDefault([
                'name' => '',
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
                'category' => 'character',
                'name' => trans('web::seat.unknown'),
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
                'name' => trans('web::seat.unknown'),
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
