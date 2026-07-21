# Changelog

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
