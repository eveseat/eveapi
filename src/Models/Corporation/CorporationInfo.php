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

namespace Seat\Eveapi\Models\Corporation;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Bookmarks\CorporationBookmark;
use Seat\Eveapi\Models\Bookmarks\CorporationBookmarkFolder;
use Seat\Eveapi\Models\Contacts\CorporationContact;
use Seat\Eveapi\Models\Contacts\CorporationContactLabel;
use Seat\Eveapi\Models\Contracts\CorporationContract;
use Seat\Eveapi\Models\Industry\CorporationIndustryJob;
use Seat\Eveapi\Models\Killmails\CorporationKillmail;
use Seat\Eveapi\Models\Market\CorporationOrder;
use Seat\Eveapi\Models\PlanetaryInteraction\CorporationCustomsOffice;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Wallet\CorporationWalletBalance;
use Seat\Eveapi\Models\Wallet\CorporationWalletJournal;
use Seat\Eveapi\Models\Wallet\CorporationWalletTransaction;

/**
 * Class CorporationInfo.
 * @package Seat\Eveapi\Models\Corporation
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
     * Make sure we cleanup on delete.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {

        // Cleanup the corporation
        $this->alliance_history()->delete();
        $this->assets()->delete();
        $this->blueprints()->delete();
        $this->bookmarks()->delete();
        $this->bookmark_folders()->delete();
        $this->contacts()->delete();
        $this->contact_labels()->delete();
        $this->container_logs()->delete();
        $this->contracts()->delete();
        $this->pocos()->delete();
        $this->divisions()->delete();
        $this->facilities()->delete();
        $this->industry_jobs()->delete();

        // TODO: Mining delete

        $this->issued_medals()->delete();
        $this->killmails()->delete();
        $this->medals()->delete();
        $this->member_limit()->delete();
        $this->member_titles()->delete();
        $this->member_tracking()->delete();
        $this->members()->delete();
        $this->orders()->delete();
        $this->outposts()->delete();
        $this->outpost_details()->delete();
        $this->outpost_services()->delete();
        $this->roles()->delete();
        $this->role_history()->delete();
        $this->shareholders()->delete();
        $this->standings()->delete();

        // TODO: Starbases & Structures

        $this->titles()->delete();
        $this->title_roles()->delete();
        $this->wallet_balances()->delete();
        $this->wallet_journal()->delete();
        $this->wallet_transactions()->delete();

        return parent::delete();
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
    public function bookmarks()
    {

        return $this->hasMany(CorporationBookmark::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookmark_folders()
    {

        return $this->hasMany(CorporationBookmarkFolder::class,
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

        return $this->hasMany(CorporationContactLabel::class,
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
    public function killmails()
    {

        return $this->hasMany(CorporationKillmail::class,
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function member_limit()
    {

        return $this->hasOne(CorporationMemberLimits::class,
            'corporation_id', 'corporation_id');
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
    public function members()
    {

        return $this->hasMany(CorporationMember::class,
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
    public function outposts()
    {

        return $this->hasMany(CorporationOutpost::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outpost_details()
    {

        return $this->hasMany(CorporationOutpostDetail::class,
            'corporation_id', 'corporation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outpost_services()
    {

        return $this->hasMany(CorporationOutpostService::class,
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function home_station()
    {

        return $this->belongsTo(UniverseStation::class,
            'home_station_id', 'station_id');
    }
}
