<?php

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountApiKeyInfoCharacters
 * @package Seat\Eveapi\Models
 */
class AccountApiKeyInfoCharacters extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'keyID', 'characterID', 'characterName', 'corporationID', 'corporationName'];

    /**
     * Returns the Key this character belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function key()
    {

        return $this->hasOne(
            'Seat\Eveapi\Models\AccountApiKeyInfo', 'keyID', 'keyID');
    }
}
