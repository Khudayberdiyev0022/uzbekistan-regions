<?php

namespace Khudayberdiyev\UzbekistanRegions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Khudayberdiyev\UzbekistanRegions\Concerns\SoatoModel;

/**
 * @property int $id
 * @property int $district_id
 * @property string $soato_id
 * @property string $name_uz
 * @property string $name_oz
 * @property string|null $name_ru
 */
class Quarter extends Model
{
  use SoatoModel;

  protected $table = 'quarters';

  protected $fillable = ['district_id', 'soato_id', 'name_uz', 'name_oz', 'name_ru'];

  public function district(): BelongsTo
  {
    return $this->belongsTo(District::class);
  }
}
