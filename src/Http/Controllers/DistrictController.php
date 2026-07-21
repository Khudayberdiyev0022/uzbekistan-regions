<?php

namespace Khudayberdiyev\UzbekistanRegions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Khudayberdiyev\UzbekistanRegions\Http\Resources\DistrictResource;
use Khudayberdiyev\UzbekistanRegions\Models\District;

class DistrictController
{
  /**
   * GET /districts?region_id=&search=&sort=&order=&per_page=
   */
  public function index(Request $request): ResourceCollection
  {
    $filters = $request->validate([
      'region_id' => ['nullable', 'integer', 'min:1'],
      'type'      => ['nullable', 'in:district,city'],
      'search'    => ['nullable', 'string', 'max:255'],
      'sort'      => ['nullable', 'in:id,name,soato_id'],
      'order'     => ['nullable', 'in:asc,desc'],
      'per_page'  => ['nullable', 'integer', 'min:1', 'max:200'],
    ]);

    $query = District::query()
      ->withCount('quarters')
      ->when($filters['region_id'] ?? null, fn ($q, $id) => $q->where('region_id', $id))
      ->ofType($filters['type'] ?? null)
      ->search($filters['search'] ?? null)
      ->sortBy($filters['sort'] ?? 'id', $filters['order'] ?? 'asc');

    return DistrictResource::collection(
      isset($filters['per_page'])
        ? $query->paginate($filters['per_page'])->withQueryString()
        : $query->get()
    );
  }

  /**
   * GET /districts/{id}
   */
  public function show(int $id): DistrictResource
  {
    return DistrictResource::make(
      District::with(['region', 'quarters'])->withCount('quarters')->findOrFail($id)
    );
  }
}
