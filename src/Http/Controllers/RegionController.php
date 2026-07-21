<?php

namespace Khudayberdiyev\UzbekistanRegions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Khudayberdiyev\UzbekistanRegions\Http\Resources\RegionResource;
use Khudayberdiyev\UzbekistanRegions\Models\Region;

class RegionController
{
  /**
   * GET /regions?search=&sort=&order=&per_page=
   */
  public function index(Request $request): ResourceCollection
  {
    $filters = $request->validate([
      'type'     => ['nullable', 'in:region,city,republic'],
      'search'   => ['nullable', 'string', 'max:255'],
      'sort'     => ['nullable', 'in:id,name,soato_id,order'],
      'order'    => ['nullable', 'in:asc,desc'],
      'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
    ]);

    $query = Region::query()
      ->withCount(['districts', 'quarters'])
      ->ofType($filters['type'] ?? null)
      ->search($filters['search'] ?? null)
      ->sortBy($filters['sort'] ?? 'id', $filters['order'] ?? 'asc');

    return RegionResource::collection(
      isset($filters['per_page'])
        ? $query->paginate($filters['per_page'])->withQueryString()
        : $query->get()
    );
  }

  /**
   * GET /regions/{id}
   */
  public function show(int $id): RegionResource
  {
    return RegionResource::make(
      Region::with('districts.quarters')->withCount(['districts', 'quarters'])->findOrFail($id)
    );
  }
}
