<?php


namespace Seat\Eveapi\Tests\Resources\Esi\Character;


use Illuminate\Http\Resources\Json\JsonResource;

class AffiliationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'alliance_id'    => $this->when($this->alliance_id !== null, $this->alliance_id),
            'character_id'   => $this->character_id,
            'corporation_id' => $this->corporation_id,
            'faction_id'     => $this->when($this->faction_id !== null, $this->faction_id),
        ];
    }
}