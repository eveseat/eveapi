<?php


namespace Seat\Eveapi\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Seat\Web\Models\Acl\Permission;

/**
 * Trait AuthorizedScope
 *
 * @package Seat\Eveapi\Traits
 */
trait AuthorizedScope
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $required_permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAuthorized(Builder $query, string $required_permission)
    {
        if (auth()->user()->hasSuperUser())
            return $query;

        $permission = new Permission([
            'title' => $required_permission,
        ]);

        // the permission is a character permission - apply filter on character_id field
        if ($permission->isCharacterScope()) {
            $character_map = collect(Arr::get(auth()->user()->getAffiliationMap(), 'char'));

            // collect only character which has either the requested permission or wildcard
            $character_ids = $character_map->filter(function ($permissions, $key) use ($permission) {
                return in_array('character.*', $permissions) || in_array($permission->title, $permissions);
            })->keys();

            return $query->whereIn(sprintf('%s.%s', $this->getTable(), 'character_id'), $character_ids);
        }

        // the permission is a corporation permission - apply filter on corporation_id field
        if ($permission->isCorporationScope()) {
            $corporation_map = collect(Arr::get(auth()->user()->getAffiliationMap(), 'corp'));

            // collect only corporation which has either the requested permission or wildcard
            $corporation_ids = $corporation_map->filter(function ($permissions, $key) use ($permission) {
                return in_array('corporation.*', $permissions) || in_array($permission->title, $permissions);
            })->keys();

            return $query->whereIn(sprintf('%s.%s', $this->getTable(), 'corporation_id'), $corporation_ids);
        }

        return $query;
    }
}
