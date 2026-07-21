# Uzbekistan Regions

*[English](README.md) · O'zbekcha*

O'zbekiston viloyatlari, tumanlari va mahallalari (SOATO) uchun Laravel paketi: modellar,
migratsiyalar, tayyor ma'lumotlar to'plami va uch tilli (uz / oz / ru) REST API.

## O'rnatish

```bash
composer require khudayberdiyev/uzbekistan-regions
```

Jadvallarni yaratib, ma'lumotlarni yuklang:

```bash
php artisan migrate
php artisan uzbekistan-regions:seed
```

Konfiguratsiyani o'zgartirmoqchi bo'lsangiz:

```bash
php artisan vendor:publish --tag=uzbekistan-regions-config
```

## Modellar bilan ishlash

```php
use Khudayberdiyev\UzbekistanRegions\Models\Region;
use Khudayberdiyev\UzbekistanRegions\Models\District;
use Khudayberdiyev\UzbekistanRegions\Models\Quarter;

$region = Region::with('districts.quarters')->find(2);

$region->getName();          // joriy locale bo'yicha nom
$region->type;               // region | city | republic
$region->name_ru;            // aniq til
$region->districts;          // tumanlar
$region->quarters;           // viloyatdagi barcha mahallalar (hasManyThrough)

$district = District::find(10);
$district->region;
$district->quarters;
$district->isCity();         // Andijon shahri yoki Andijon tumani
```

Har bir viloyat va tuman turi bo'yicha ajratilgan — select'da faqat keragini ko'rsatish uchun:

```php
District::ofType(District::TYPE_CITY)->get();     // 34 ta shahar
District::ofType(District::TYPE_DISTRICT)->get(); // 175 ta tuman
Region::ofType(Region::TYPE_REGION)->get();       // 12 ta viloyat
```

Tur SOATO kodining o'zidan olinadi — beshinchi raqam shahar uchun `4`, tuman uchun `2`.

Uchala model ikkita scope bilan keladi — API ham aynan shulardan foydalanadi:

```php
// joriy tildagi nom bo'yicha registrga sezgir bo'lmagan qidiruv
District::search('and')->get();

// sort=name berilganda joriy til ustuni bo'yicha tartiblanadi
Quarter::sortBy('name', 'desc')->get();
```

O'z modelingizga bog'lash:

```php
public function district(): BelongsTo
{
  return $this->belongsTo(\Khudayberdiyev\UzbekistanRegions\Models\District::class);
}
```

## API

Paket avtomatik ravishda quyidagi read-only endpointlarni ro'yxatdan o'tkazadi:

| Method | URI                    | Tavsif                                     |
|--------|------------------------|--------------------------------------------|
| GET    | `/api/v1/regions`      | Viloyatlar ro'yxati                        |
| GET    | `/api/v1/regions/{id}` | Viloyat + tumanlar + mahallalar            |
| GET    | `/api/v1/districts`    | Tumanlar (`?region_id=` bilan filtrlanadi) |
| GET    | `/api/v1/districts/{id}` | Tuman + viloyat + mahallalar             |
| GET    | `/api/v1/quarters`     | Mahallalar (`?district_id=`, `?region_id=`) |
| GET    | `/api/v1/quarters/{id}` | Mahalla + tuman + viloyat                 |

Query parametrlar: `type` (viloyatlar uchun `region` / `city` / `republic`, tumanlar uchun
`district` / `city`), `search`, `sort` (`id`, `name`, `soato_id`, `order`),
`order` (`asc` / `desc`), `per_page` (berilsa javob paginatsiya bilan qaytadi).

Til `Accept-Language` header orqali tanlanadi — `uz` (default), `oz`, `ru`.

```bash
curl -H "Accept-Language: ru" "https://example.test/api/v1/districts?region_id=2&search=Ан"
```

Javob — Laravel'ning standart resurs formati:

```json
{
  "data": [
    { "id": 2, "soato_id": "1703", "type": "region", "name": "Andijon viloyati", "order": 1, "districts_count": 14, "quarters_count": 305 }
  ]
}
```

`per_page` berilganda javobga standart `links` va `meta` bloklari qo'shiladi.

## Konfiguratsiya

`config/uzbekistan-regions.php`:

```php
'routes' => [
  'enabled'    => true,          // faqat modellar kerak bo'lsa false qiling
  'prefix'     => 'api/v1',
  'middleware' => ['api', SetLocale::class],
],
'locales'        => ['uz', 'oz', 'ru'],
'default_locale' => 'uz',
'connection'     => null,        // alohida DB connection kerak bo'lsa
'data_path'      => null,        // o'z JSON fayllaringiz bo'lsa
```

## Testlar

```bash
composer install
vendor/bin/phpunit
```

## Litsenziya

MIT.
