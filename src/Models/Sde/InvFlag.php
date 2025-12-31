<?php

namespace Seat\Eveapi\Models\Sde;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\IsReadOnly;

/**
 * Class InvFlag.
 */
class InvFlag extends Model
{
    use IsReadOnly;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'invFlags';

    /**
     * @var string
     */
    protected $primaryKey = 'flagID';

    /**
     * @var bool
     */
    public $timestamps = false;
}
