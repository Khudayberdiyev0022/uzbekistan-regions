<?php

namespace Khudayberdiyev\UzbekistanRegions\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Khudayberdiyev\UzbekistanRegions\Models\District */
class DistrictResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id'             => $this->id,
      'soato_id'       => $this->soato_id,
      'name'           => $this->getName(),
      'order'          => $this->order,
      'region_id'      => $this->region_id,
      'quarters_count' => $this->whenNotNull($this->quarters_count),
      'region'         => RegionResource::make($this->whenLoaded('region')),
      'quarters'       => QuarterResource::collection($this->whenLoaded('quarters')),
    ];
  }
}
