<?php

namespace Khudayberdiyev\UzbekistanRegions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Khudayberdiyev\UzbekistanRegions\Concerns\SoatoModel;

/**
 * @property int $id
 * @property string $soato_id
 * @property string $type
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

  /** A viloyat. */
  public const TYPE_REGION = 'region';

  /** Tashkent, the only city that is a region of its own. */
  public const TYPE_CITY = 'city';

  /** Karakalpakstan. */
  public const TYPE_REPUBLIC = 'republic';

  protected $table = 'regions';

  protected $fillable = ['soato_id', 'type', 'name_uz', 'name_oz', 'name_ru', 'order'];

  /**
   * Narrow the query down to one type, ignoring a null or empty value.
   */
  public function scopeOfType($query, ?string $type)
  {
    return blank($type) ? $query : $query->where('type', $type);
  }

  public function districts(): HasMany
  {
    return $this->hasMany(District::class);
  }

  public function quarters(): HasManyThrough
  {
    return $this->hasManyThrough(Quarter::class, District::class);
  }
}
