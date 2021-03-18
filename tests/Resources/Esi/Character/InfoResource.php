<?php


namespace Seat\Eveapi\Tests\Resources\Esi\Character;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Info
 * @package Seat\Eveapi\Tests\Resources\Esi\Character
 */
class InfoResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'alliance_id'     => $this->when($this->alliance_id !== null, $this->alliance_id),
            'ancestry_id'     => $this->when($this->ancestry_id !== null, $this->ancestry_id),
            'birthday'        => $this->birthday,
            'bloodline_id'    => $this->bloodline_id,
            'corporation_id'  => $this->corporation_id,
            'description'     => $this->when($this->description !== null, $this->description),
            'faction_id'      => $this->when($this->faction_id !== null, $this->faction_id),
            'gender'          => $this->gender,
            'name'            => $this->name,
            'race_id'         => $this->race_id,
            'security_status' => $this->when($this->security_status !== null, $this->security_status),
            'title'           => $this->when($this->title !== null, $this->title),
        ];
    }
}
