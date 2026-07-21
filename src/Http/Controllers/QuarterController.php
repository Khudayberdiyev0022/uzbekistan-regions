<?php

namespace Khudayberdiyev\UzbekistanRegions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Khudayberdiyev\UzbekistanRegions\Http\Resources\QuarterResource;
use Khudayberdiyev\UzbekistanRegions\Models\Quarter;

class QuarterController
{
  /**
   * GET /quarters?region_id=&district_id=&search=&sort=&order=&per_page=
   */
  public function index(Request $request): ResourceCollection
  {
    $filters = $request->validate([
      'region_id'   => ['nullable', 'integer', 'min:1'],
      'district_id' => ['nullable', 'integer', 'min:1'],
      'search'      => ['nullable', 'string', 'max:255'],
      'sort'        => ['nullable', 'in:id,name,soato_id,order'],
      'order'       => ['nullable', 'in:asc,desc'],
      'per_page'    => ['nullable', 'integer', 'min:1', 'max:200'],
    ]);

    $query = Quarter::query()
      ->when($filters['district_id'] ?? null, fn ($q, $id) => $q->where('district_id', $id))
      ->when($filters['region_id'] ?? null, fn ($q, $id) => $q->whereRelation('district', 'region_id', $id))
      ->search($filters['search'] ?? null)
      ->sortBy($filters['sort'] ?? 'id', $filters['order'] ?? 'asc');

    return QuarterResource::collection(
      isset($filters['per_page'])
        ? $query->paginate($filters['per_page'])->withQueryString()
        : $query->get()
    );
  }

  /**
   * GET /quarters/{id}
   */
  public function show(int $id): QuarterResource
  {
    return QuarterResource::make(
      Quarter::with('district.region')->findOrFail($id)
    );
  }
}
