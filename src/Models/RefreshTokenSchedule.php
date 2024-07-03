<?php

namespace Seat\Eveapi\Models;

use Carbon\Carbon;
use Seat\Services\Models\ExtensibleModel;

/**
 * @property int $character_id
 * @property Carbon $last_update
 * @property int $update_interval
 * @property RefreshToken $token
 */
class RefreshTokenSchedule extends ExtensibleModel
{
    protected $table = 'refresh_token_schedules';
    protected $primaryKey = 'character_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'last_update' => 'datetime',
    ];
    public function token()
    {
        return $this->hasOne(RefreshToken::class,'character_id');
    }
}