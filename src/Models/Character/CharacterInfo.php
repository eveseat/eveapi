<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

namespace Seat\Eveapi\Models\Character;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Bookmarks\CharacterBookmark;
use Seat\Eveapi\Models\Bookmarks\CharacterBookmarkFolder;
use Seat\Eveapi\Models\Calendar\CharacterCalendarEvent;
use Seat\Eveapi\Models\Clones\CharacterClone;
use Seat\Eveapi\Models\Clones\CharacterImplant;
use Seat\Eveapi\Models\Clones\CharacterJumpClone;
use Seat\Eveapi\Models\Contacts\CharacterContact;
use Seat\Eveapi\Models\Contacts\CharacterLabel;
use Seat\Eveapi\Models\Contracts\CharacterContract;
use Seat\Eveapi\Models\Corporation\CorporationTitle;
use Seat\Eveapi\Models\Fittings\CharacterFitting;
use Seat\Eveapi\Models\Industry\CharacterIndustryJob;
use Seat\Eveapi\Models\Industry\CharacterMining;
use Seat\Eveapi\Models\Location\CharacterLocation;
use Seat\Eveapi\Models\Location\CharacterOnline;
use Seat\Eveapi\Models\Location\CharacterShip;
use Seat\Eveapi\Models\Mail\MailHeader;
use Seat\Eveapi\Models\Market\CharacterOrder;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Skills\CharacterAttribute;
use Seat\Eveapi\Models\Wallet\CharacterWalletBalance;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;
use Seat\Eveapi\Models\Wallet\CharacterWalletTransaction;
use Seat\Eveapi\Pivot\Character\CharacterTitle;
use Seat\Eveapi\Traits\AuthorizedScope;
use Seat\Services\Traits\NotableTrait;
use Seat\Web\Models\User;

/**
 * Class CharacterInfo.
 * @package Seat\Eveapi\Models\Character
 *
 * @SWG\Definition(
 *     description="Character Info",
 *     title="CharacterInfo",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="name",
 *     description="Character name"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="description",
 *     description="Character biography"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="corporation_id",
 *     description="Character corporation identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="alliance_id",
 *     description="Character alliance identifier"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="birthday",
 *     description="Character birthday"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"male", "female"},
 *     property="gender",
 *     description="Character gender"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="race_id",
 *     description="Character race identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="bloodline_id",
 *     description="Character bloodline identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="ancenstry_id",
 *     description="Character ancenstry identifier"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="security_status",
 *     description="Character security status"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="faction_id",
 *     description="Character faction identifier"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when record has been created into SeAT"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when record has been updated into SeAT"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="balance",
 *     description="Character wallet balance"
 * )
 *
 * @SWG\Property(
 *     type="object",
 *     property="skillpoints",
 *     description="",
 *     @SWG\Property(
 *          type="number",
 *          format="int64",
 *          property="total_sp",
 *          description="The total skill points owned by the character"
 *     ),
 *     @SWG\Property(
 *          type="number",
 *          format="int64",
 *          property="unallocated_sp",
 *          description="The total skill points not allocated for this character"
 *     )
 * )
 */
class CharacterInfo extends Model
{
    use AuthorizedScope;
    use NotableTrait;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var string
     */
    protected $primaryKey = 'character_id';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * Make sure we cleanup on delete.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {

        // Cleanup the character
        $this->agent_research()->delete();
        $this->assets()->delete();
        $this->balance()->delete();
        $this->blueprints()->delete();
        $this->bookmarks()->delete();
        $this->bookmark_folders()->delete();
        $this->calendar_events()->delete();
        $this->clones()->delete();
        $this->contact_labels()->delete();
        $this->contacts()->delete();
        $this->contracts()->delete();
        $this->corporation_history()->delete();
        $this->fatigue()->delete();
        $this->fittings()->delete();
        $this->implants()->delete();
        $this->industry()->delete();
        $this->jump_clones()->delete();
        $this->killmails()->delete();
        $this->location()->delete();
        $this->medals()->delete();
        $this->mining()->delete();
        $this->notifications()->delete();
        $this->online()->delete();
        $this->orders()->delete();

        // TODO: PI Cleanup

        $this->standings()->delete();
        $this->stats()->delete();
        $this->titles()->delete();
        $this->corporation_roles()->delete();
        $this->pilot_attributes()->delete();
        $this->skills()->delete();
        $this->ship()->delete();
        $this->wallet_journal()->delete();
        $this->wallet_transactions()->delete();

        return parent::delete();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agent_research()
    {

        return $this->hasMany(CharacterAgentResearch::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function affiliation()
    {
        return $this->hasOne(CharacterAffiliation::class, 'character_id', 'character_id')
            ->withDefault([
                'corporation_id' => 0,
                'alliance_id' => 0,
                'faction_id' => 0,
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {

        return $this->hasMany(CharacterAsset::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function balance()
    {

        return $this->belongsTo(CharacterWalletBalance::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blueprints()
    {

        return $this->hasMany(CharacterBlueprint::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookmarks()
    {

        return $this->hasMany(CharacterBookmark::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookmark_folders()
    {

        return $this->hasMany(CharacterBookmarkFolder::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calendar_events()
    {

        return $this->hasMany(CharacterCalendarEvent::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clones()
    {

        return $this->hasMany(CharacterClone::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contact_labels()
    {

        return $this->hasMany(CharacterLabel::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {

        return $this->hasMany(CharacterContact::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts()
    {

        return $this->hasMany(CharacterContract::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function corporation_history()
    {

        return $this->hasMany(CharacterCorporationHistory::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fatigue()
    {

        return $this->hasOne(CharacterFatigue::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fittings()
    {

        return $this->hasMany(CharacterFitting::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function implants()
    {

        return $this->hasMany(CharacterImplant::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function industry()
    {

        return $this->hasMany(CharacterIndustryJob::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jump_clones()
    {

        return $this->hasMany(CharacterJumpClone::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {

        return $this->hasOne(CharacterLocation::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mails()
    {
        return $this->belongsToMany(MailHeader::class, 'mail_recipients', 'recipient_id', 'mail_id')
            ->withPivot('is_read', 'labels');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medals()
    {

        return $this->hasMany(CharacterMedal::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mining()
    {

        return $this->hasMany(CharacterMining::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {

        return $this->hasMany(CharacterNotification::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function online()
    {

        return $this->hasOne(CharacterOnline::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {

        return $this->hasMany(CharacterOrder::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function refresh_token()
    {
        return $this->hasOne(RefreshToken::class, 'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function standings()
    {

        return $this->hasMany(CharacterStanding::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stats()
    {

        return $this->hasMany(CharacterStats::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function titles()
    {

        return $this->belongsToMany(CorporationTitle::class)->using(CharacterTitle::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function corporation_roles()
    {

        return $this->hasMany(CharacterRole::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pilot_attributes()
    {

        return $this->hasMany(CharacterAttribute::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skills()
    {

        return $this->hasMany(CharacterSkill::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function skillpoints()
    {
        return $this->hasOne(CharacterInfoSkill::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ship()
    {

        return $this->hasOne(CharacterShip::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallet_journal()
    {

        return $this->hasMany(CharacterWalletJournal::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallet_transactions()
    {

        return $this->hasMany(CharacterWalletTransaction::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @deprecated 4.0.0
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, RefreshToken::class, 
            'character_id', 'id', 'character_id', 'user_id');
    }

    /**
     * @return \Seat\Eveapi\Models\Character\CharacterCorporationHistory
     * @deprecated 4.0.0
     */
    public function getCurrentCorporationAttribute()
    {

        return CharacterCorporationHistory::where('character_id', $this->character_id)
            ->orderBy('record_id', 'desc')
            ->first();
    }
}
