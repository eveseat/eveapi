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

namespace Seat\Eveapi\Models\Character;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Assets\CharacterAsset;
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
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanet;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Skills\CharacterAttribute;
use Seat\Eveapi\Models\Skills\CharacterSkillQueue;
use Seat\Eveapi\Models\Wallet\CharacterWalletBalance;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;
use Seat\Eveapi\Models\Wallet\CharacterWalletTransaction;
use Seat\Eveapi\Pivot\Character\CharacterTitle;
use Seat\Services\Traits\NotableTrait;
use Seat\Web\Models\User;

/**
 * Class CharacterInfo.
 * @package Seat\Eveapi\Models\Character
 */
class CharacterInfo extends Model
{
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNpc($query)
    {
        return $query->whereBetween('character_id', [3000000, 3999999]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePlayer($query)
    {
        return $query->whereNotBetween('character_id', [3000000, 3999999]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function affiliation()
    {
        return $this->hasOne(CharacterAffiliation::class, 'character_id', 'character_id')
            ->withDefault();
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
    public function calendar_events()
    {

        return $this->hasMany(CharacterCalendarEvent::class,
            'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function clone()
    {

        return $this->hasOne(CharacterClone::class, 'character_id', 'character_id')
            ->withDefault();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function colonies()
    {
        return $this->hasMany(CharacterPlanet::class, 'character_id', 'character_id');
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

        return $this->hasMany(CharacterCorporationHistory::class, 'character_id', 'character_id')
            ->orderByDesc('record_id');
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fatigue()
    {

        return $this->hasOne(CharacterFatigue::class, 'character_id', 'character_id')
            ->withDefault();
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
    public function pilot_attributes()
    {

        return $this->hasOne(CharacterAttribute::class, 'character_id', 'character_id')
            ->withDefault();
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skills()
    {

        return $this->hasMany(CharacterSkill::class,
            'character_id', 'character_id')->with('type', 'type.group');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skill_queue()
    {
        return $this->hasMany(CharacterSkillQueue::class, 'character_id', 'character_id')
            ->orderBy('queue_position');
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function titles()
    {

        return $this->belongsToMany(CorporationTitle::class)->using(CharacterTitle::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     * @deprecated 4.0.0
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, RefreshToken::class,
            'character_id', 'id', 'character_id', 'user_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
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
