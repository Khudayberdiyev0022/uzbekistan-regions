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
 * @property string $type
 * @property string $name_uz
 * @property string $name_oz
 * @property string|null $name_ru
 * @property int $order
 * @property int|null $quarters_count
 */
class District extends Model
{
  use SoatoModel;

  /** A tuman. */
  public const TYPE_DISTRICT = 'district';

  /** A city of regional or republican subordination. */
  public const TYPE_CITY = 'city';

  protected $table = 'districts';

  protected $fillable = ['region_id', 'soato_id', 'type', 'name_uz', 'name_oz', 'name_ru', 'order'];

  public function isCity(): bool
  {
    return $this->type === self::TYPE_CITY;
  }

  /**
   * Narrow the query down to one type, ignoring a null or empty value.
   */
  public function scopeOfType($query, ?string $type)
  {
    return blank($type) ? $query : $query->where('type', $type);
  }

  public function region(): BelongsTo
  {
    return $this->belongsTo(Region::class);
  }

  public function quarters(): HasMany
  {
    return $this->hasMany(Quarter::class);
  }
}
