<?php

namespace Khudayberdiyev\UzbekistanRegions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Khudayberdiyev\UzbekistanRegions\Models\Quarter */
class QuarterResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id'          => $this->id,
      'soato_id'    => $this->soato_id,
      'name'        => $this->getName(),
      'order'       => $this->order,
      'district_id' => $this->district_id,
      'district'    => DistrictResource::make($this->whenLoaded('district')),
    ];
  }
}
