<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountApiKeyInfo
 * @package Seat\Eveapi\Models
 */
class AccountApiKeyInfo extends Model
{

    /**
     * @var string
     */
    protected $primaryKey = 'keyID';

    /**
     * @var array
     */
    protected $fillable = ['keyID', 'accessMask', 'type', 'expires'];

    /**
     * Returns the characters for the key
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function characters()
    {

        return $this->hasMany(
            'Seat\Eveapi\Models\AccountApiKeyInfoCharacters', 'keyID', 'keyID');
    }
}
