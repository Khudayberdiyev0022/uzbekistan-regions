# Uzbekistan Regions

*English · [O'zbekcha](README.uz.md)*

Regions, districts and quarters (mahalla) of Uzbekistan for Laravel: Eloquent models, migrations,
the full SOATO dataset and a ready-to-use REST API — in Uzbek Latin, Uzbek Cyrillic and Russian.

The dataset ships with the package: **14 regions, 210 districts, 2 641 quarters**.

## Installation

```bash
composer require khudayberdiyev/uzbekistan-regions
```

Create the tables and load the data:

```bash
php artisan migrate
php artisan uzbekistan-regions:seed
```

To change any default, publish the config file:

```bash
php artisan vendor:publish --tag=uzbekistan-regions-config
```

## Working with the models

```php
use Khudayberdiyev\UzbekistanRegions\Models\Region;
use Khudayberdiyev\UzbekistanRegions\Models\District;
use Khudayberdiyev\UzbekistanRegions\Models\Quarter;

$region = Region::with('districts.quarters')->find(2);

$region->getName();          // name in the active locale
$region->name_ru;            // or an explicit language
$region->districts;          // districts of the region
$region->quarters;           // every quarter in the region (hasManyThrough)

$district = District::find(10);
$district->region;
$district->quarters;
```

All three models expose two query scopes — the API is built on the very same ones:

```php
// case insensitive prefix search on the name in the active locale
District::search('and')->get();

// "name" is resolved to the column of the active locale
Quarter::sortBy('name', 'desc')->get();
```

Referencing them from your own models:

```php
public function district(): BelongsTo
{
  return $this->belongsTo(\Khudayberdiyev\UzbekistanRegions\Models\District::class);
}
```

## API

The package registers these read-only endpoints automatically:

| Method | URI                      | Returns                                     |
|--------|--------------------------|---------------------------------------------|
| GET    | `/api/v1/regions`        | list of regions                             |
| GET    | `/api/v1/regions/{id}`   | region + districts + quarters               |
| GET    | `/api/v1/districts`      | districts, filterable by `?region_id=`      |
| GET    | `/api/v1/districts/{id}` | district + region + quarters                |
| GET    | `/api/v1/quarters`       | quarters, filterable by `?district_id=` / `?region_id=` |
| GET    | `/api/v1/quarters/{id}`  | quarter + district + region                 |

Query parameters: `search`, `sort` (`id`, `name`, `soato_id`, `order`), `order` (`asc` / `desc`)
and `per_page` — when present, the response is paginated.

The language is selected with the `Accept-Language` header: `uz` (default), `oz`, `ru`.

```bash
curl -H "Accept-Language: ru" "https://example.test/api/v1/districts?region_id=2&search=Ан"
```

Responses use the standard Laravel resource format:

```json
{
  "data": [
    { "id": 2, "soato_id": "1703", "name": "Andijon viloyati", "order": 1, "districts_count": 14, "quarters_count": 305 }
  ]
}
```

With `per_page` the usual `links` and `meta` blocks are added.

## Configuration

`config/uzbekistan-regions.php`:

```php
'routes' => [
  'enabled'    => true,          // set to false if you only need the models
  'prefix'     => 'api/v1',
  'middleware' => ['api', SetLocale::class],
],
'locales'        => ['uz', 'oz', 'ru'],
'default_locale' => 'uz',
'connection'     => null,        // a dedicated database connection, if you use one
'data_path'      => null,        // your own JSON files, if you maintain them
```

## Tests

```bash
composer install
vendor/bin/phpunit
```

## License

MIT.
