<?php

namespace Khudayberdiyev\UzbekistanRegions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Khudayberdiyev\UzbekistanRegions\Concerns\SoatoModel;

/**
 * @property int $id
 * @property string $soato_id
 * @property string $name_uz
 * @property string $name_oz
 * @property string|null $name_ru
 * @property int $order
 * @property int|null $districts_count
 * @property int|null $quarters_count
 */
class Region extends Model
{
  use SoatoModel;

  protected $table = 'regions';

  protected $fillable = ['soato_id', 'name_uz', 'name_oz', 'name_ru', 'order'];

  public function districts(): HasMany
  {
    return $this->hasMany(District::class);
  }

  public function quarters(): HasManyThrough
  {
    return $this->hasManyThrough(Quarter::class, District::class);
  }
}
