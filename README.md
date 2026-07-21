# Uzbekistan Regions

*English · [O'zbekcha](README.uz.md)*

[![tests](https://github.com/Khudayberdiyev0022/uzbekistan-regions/actions/workflows/tests.yml/badge.svg)](https://github.com/Khudayberdiyev0022/uzbekistan-regions/actions/workflows/tests.yml)

Regions, districts and quarters (mahalla) of Uzbekistan for Laravel: Eloquent models, migrations,
the full SOATO dataset and a ready-to-use REST API — in Uzbek Latin, Uzbek Cyrillic and Russian.

The dataset ships with the package: **14 regions, 209 districts and cities, 2 641 quarters**.

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
$region->type;               // region | city | republic
$region->name_ru;            // or an explicit language
$region->districts;          // districts of the region
$region->quarters;           // every quarter in the region (hasManyThrough)

$district = District::find(10);
$district->region;
$district->quarters;
$district->isCity();         // Andijon shahri vs Andijon tumani
```

Every region and district is classified, so a picker can show only what it needs:

```php
District::ofType(District::TYPE_CITY)->get();     // the 34 cities
District::ofType(District::TYPE_DISTRICT)->get(); // the 175 tumans
Region::ofType(Region::TYPE_REGION)->get();       // the 12 viloyats
```

The type comes from the SOATO code itself — the fifth digit is `4` for a city and `2` for a
district — so it stays correct as the dataset grows.

All three models expose two query scopes — the API is built on the very same ones:

```php
// case insensitive search anywhere in the name, in the active locale
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

Query parameters: `type` (`region` / `city` / `republic` for regions, `district` / `city` for
districts), `search`, `sort` (`id`, `name`, `soato_id`, `order`), `order` (`asc` / `desc`) and
`per_page` — when present, the response is paginated.

The language is selected with the `Accept-Language` header: `uz` (default), `oz`, `ru`.

```bash
curl -H "Accept-Language: ru" "https://example.test/api/v1/districts?region_id=2&search=Ан"
```

Responses use the standard Laravel resource format:

```json
{
  "data": [
    { "id": 2, "soato_id": "1703", "type": "region", "name": "Andijon viloyati", "order": 1, "districts_count": 14, "quarters_count": 305 }
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

CI runs the suite on PHP 8.2, 8.3 and 8.4 against Laravel 12, and once more against
PostgreSQL — the search scope uses `ILIKE` there and cased `LIKE` variants everywhere else.
Laravel 11 is still allowed by the version constraint but is no longer covered by CI: every
11.x release now carries unpatched security advisories, and Composer refuses to install it.

## License

MIT.
