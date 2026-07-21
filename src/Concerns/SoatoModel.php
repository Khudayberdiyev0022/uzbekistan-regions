<?php

namespace Khudayberdiyev\UzbekistanRegions\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Shared behaviour of the Region, District and Quarter models:
 * the three name columns and the two query scopes behind the API.
 */
trait SoatoModel
{
  /**
   * The name in the active locale, falling back to the default one.
   */
  public function getName(): ?string
  {
    return $this->{'name_'.static::locale()}
      ?? $this->{'name_'.config('uzbekistan-regions.default_locale', 'uz')};
  }

  /**
   * The active locale, restricted to the ones the dataset actually has.
   */
  public static function locale(): string
  {
    $locale  = app()->getLocale();
    $locales = (array) config('uzbekistan-regions.locales', ['uz', 'oz', 'ru']);

    return in_array($locale, $locales, true)
      ? $locale
      : (string) config('uzbekistan-regions.default_locale', 'uz');
  }

  /**
   * Case insensitive prefix search on the localized name.
   */
  public function scopeSearch(Builder $query, ?string $term): Builder
  {
    if (blank($term)) {
      return $query;
    }

    $column = 'name_'.static::locale();

    if ($query->getConnection()->getDriverName() === 'pgsql') {
      return $query->where($column, 'ilike', $term.'%');
    }

    return $query->whereRaw(
      'LOWER('.$query->getGrammar()->wrap($column).') LIKE ?',
      [mb_strtolower($term).'%']
    );
  }

  /**
   * Sorting where "name" means the localized name column.
   */
  public function scopeSortBy(Builder $query, ?string $column, ?string $direction = 'asc'): Builder
  {
    $column = in_array($column, ['id', 'name', 'soato_id', 'order'], true) ? $column : 'id';

    if ($column === 'name') {
      $column = 'name_'.static::locale();
    }

    return $query->orderBy($column, strtolower((string) $direction) === 'desc' ? 'desc' : 'asc');
  }

  public function getConnectionName()
  {
    return config('uzbekistan-regions.connection') ?: parent::getConnectionName();
  }
}
