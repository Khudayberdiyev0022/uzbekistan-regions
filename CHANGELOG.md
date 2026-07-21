# Changelog

## v1.1.1

Fixes `search`, which did not work at all in Cyrillic outside PostgreSQL.

- On SQLite and MySQL the scope compared `LOWER(name) LIKE lower(term)`. SQLite's `LOWER()`
  folds ASCII only, so `LOWER('Андижан')` stays `'Андижан'` while the needle is lowercased —
  nothing ever matched in the `ru` and `oz` locales. The scope now tries the casings the data
  actually uses. PostgreSQL was unaffected, because it uses `ILIKE`.
- Matching is no longer anchored to the start of the name. Searching `Андижан` could not find
  `город Андижан`, the form v1.1.0 introduced for cities.
- `%` and `_` typed into `search` are now stripped instead of acting as wildcards.

## v1.1.0

**Upgrading from v1.0.x requires two commands**, because both the schema and the data changed:

```bash
php artisan migrate
php artisan uzbekistan-regions:seed
```

- Added a `type` column to `regions` (`region` / `city` / `republic`) and to `districts`
  (`district` / `city`), derived from the SOATO code itself — the fifth digit is `4` for a
  city and `2` for a district. 34 of the 209 districts are cities.
- Added `District::ofType()`, `Region::ofType()` and `District::isCity()`, the model constants
  behind them, and a `?type=` filter on the `/regions` and `/districts` endpoints. The `type`
  is exposed in the API responses.
- Cities are now named the way the classifier writes them: `Andijon` became `Andijon shahri`
  / `Андижон шаҳри` / `город Андижан`, matching how `Toshkent shahri` was already stored.
  This affects 34 records and is the reason for the minor version bump — a region and its
  capital can no longer collide in a list.

Verified against the ORS classifier (https://tasnif.joriy.uz): the name differences dropped
from 36 to 11, and the remaining ones are mostly typos on the ORS side (`Shaxrixon`,
`Shayxontoxur`). Three are worth a second look against the official classifier before the next
release: `1703209` Bo'z vs Bo'ston, `1727250` Piskent vs Pskent, `1727401` Nurafshon shahri vs
tumani.

Coordinates and the English and Karakalpak names were considered and deliberately left out.
The obvious source, ORS, carries no license at all, which makes its data all-rights-reserved;
OpenStreetMap data is ODbL, whose share-alike terms would clash with the MIT license of this
package.

## v1.0.1

Data fixes only — no code or API changes.

- Removed `1726260260` "Toshkent shahrining tumanlari", a list heading that had leaked into
  the districts data as if it were a district. Districts now number **209**.
- Corrected two malformed SOATO codes in Namangan region, where the parent code had been
  concatenated with the district number: `1714401365` → `1714410` (Davlatobod tumani) and
  `1714401367` → `1714415` (Yangi Namangan tumani). Both districts are now found by SOATO
  code, and both match the ORS classifier.
- Renamed `1724414` from "Baxt shaxar" / "Бахт шахар" / "город Бахт" to "Baxt" / "Бахт" /
  "Бахт", in line with how every other city is written in this dataset.
- Replaced Latin homoglyphs inside Cyrillic names — a Latin `p` standing in for Cyrillic `р`
  in 120 records, plus one Latin `C`. "Чиpчик" and "Маpгилан" could not be found by a Russian
  search before this fix.

Verified against the ORS classifier (https://tasnif.joriy.uz): 14 of 14 regions and 208 of
208 districts now match by SOATO code. `1724414` (Baxt) exists here but not in ORS.

Known and deliberately left for `v1.1.0`, since it changes values consumers may display:
cities are stored without the "shahri" suffix ("Qarshi", not "Qarshi shahri"), which makes a
region and its capital indistinguishable by name alone.

## v1.0.0

First release: Region, District and Quarter models with `search` / `sortBy` scopes,
migrations, the SOATO dataset (14 regions, 210 districts, 2 641 quarters) in Uzbek Latin,
Uzbek Cyrillic and Russian, a seeder command and a read-only REST API.
