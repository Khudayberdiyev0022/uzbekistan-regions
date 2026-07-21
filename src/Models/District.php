<?php

namespace Khudayberdiyev\UzbekistanRegions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Khudayberdiyev\UzbekistanRegions\Concerns\SoatoModel;

/**
 * @property int $id
 * @property int $region_id
 * @property string $soato_id
 * @property string $name_uz
 * @property string $name_oz
 * @property string|null $name_ru
 * @property int $order
 * @property int|null $quarters_count
 */
class District extends Model
{
  use SoatoModel;

  protected $table = 'districts';

  protected $fillable = ['region_id', 'soato_id', 'name_uz', 'name_oz', 'name_ru', 'order'];

  public function region(): BelongsTo
  {
    return $this->belongsTo(Region::class);
  }

  public function quarters(): HasMany
  {
    return $this->hasMany(Quarter::class);
  }
}
