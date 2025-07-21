<?php

namespace Seat\Eveapi\Models\Killmails;

use Illuminate\Database\Eloquent\Relations\Pivot;

class KillmailVictimItem extends Pivot
{
    /**
     * @var string
     */
    protected $table = 'killmail_victim_items';

    /**
     * @var bool
     */
    public $timestamps = false;
}