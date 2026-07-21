# Where this data comes from

The underlying facts — the names of the regions, districts and quarters and their SOATO
(MHOBT) codes — come from the administrative-territorial classifier of Uzbekistan, maintained
by the Agency of Statistics. Facts like these are not anyone's property.

The particular compilation in `regions.json`, `districts.json` and `quarters.json` was built
from **[MIMAXUZ/uzbekistan-regions-data](https://github.com/MIMAXUZ/uzbekistan-regions-data)**,
taken in **April 2025**, when that repository still published the quarters. It is licensed
GPL-3.0 there, while this package is MIT — see "Licensing" below.

## What was changed here

The dataset is not a copy. Since it was taken, this package has:

- removed `1726260260` "Toshkent shahrining tumanlari", a list heading that was stored as if
  it were a district;
- corrected two malformed SOATO codes, `1714401365` → `1714410` and `1714401367` → `1714415`;
- replaced Latin homoglyphs inside Cyrillic names in 121 records, where a Latin `p` stood in
  for Cyrillic `р` and made those names unsearchable in Russian;
- classified every region and district by `type`, derived from the SOATO code;
- renamed the 34 cities the way the classifier writes them — `Andijon` → `Andijon shahri`.

Each change is verifiable against the ORS classifier at https://tasnif.joriy.uz using
`scripts/compare-with-ors.php`. As of v1.1.1, all 14 regions and 208 of the 209 districts
match it by SOATO code.

## Licensing

The code in this package is MIT. The data originates from a GPL-3.0 repository, and that
question has not been settled with its author yet. If the licenses cannot be reconciled, this
package will rebuild the dataset from the official classifier rather than leave the matter
open.

If you maintain the upstream repository and want the attribution worded differently, please
open an issue.
