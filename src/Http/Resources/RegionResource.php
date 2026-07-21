<?php

namespace Khudayberdiyev\UzbekistanRegions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Khudayberdiyev\UzbekistanRegions\Models\Region */
class RegionResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id'              => $this->id,
      'soato_id'        => $this->soato_id,
      'type'            => $this->type,
      'name'            => $this->getName(),
      'districts_count' => $this->whenNotNull($this->districts_count),
      'quarters_count'  => $this->whenNotNull($this->quarters_count),
      'districts'       => DistrictResource::collection($this->whenLoaded('districts')),
    ];
  }
}
