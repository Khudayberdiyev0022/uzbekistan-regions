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
   * Case insensitive search anywhere in the localized name.
   *
   * Matching is deliberately not anchored to the start: Russian city names carry a
   * "город " prefix, which would otherwise hide them from the obvious search term.
   */
  public function scopeSearch(Builder $query, ?string $term): Builder
  {
    if (blank($term)) {
      return $query;
    }

    // LIKE wildcards typed by a user would only surprise them here.
    $term   = str_replace(['%', '_'], '', $term);
    $column = 'name_'.static::locale();

    if ($query->getConnection()->getDriverName() === 'pgsql') {
      return $query->where($column, 'ilike', '%'.$term.'%');
    }

    // SQLite folds case for ASCII only, so LOWER() leaves "Андижан" untouched and a
    // lowercase needle never matches it. Try the casings the data actually uses.
    $variants = array_unique([
      $term,
      mb_strtolower($term),
      mb_convert_case(mb_strtolower($term), MB_CASE_TITLE, 'UTF-8'),
    ]);

    return $query->where(function (Builder $query) use ($column, $variants) {
      foreach ($variants as $variant) {
        $query->orWhere($column, 'like', '%'.$variant.'%');
      }
    });
  }

  /**
   * Sorting where "name" means the localized name column.
   */
  public function scopeSortBy(Builder $query, ?string $column, ?string $direction = 'asc'): Builder
  {
    $column = in_array($column, ['id', 'name', 'soato_id'], true) ? $column : 'id';

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
