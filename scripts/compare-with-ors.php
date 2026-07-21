<?php

/**
 * Compares the dataset shipped with this package against the ORS service
 * (https://tasnif.joriy.uz, https://github.com/ijodkor/ors).
 *
 * Both sides identify a unit by its SOATO code — ours in "soato_id", theirs in "id" —
 * so the comparison is exact and does not rely on matching names.
 *
 * The output is a review aid, not an automatic update: ORS is maintained by hand as
 * well, so every difference has to be checked against the official classifier before
 * it goes into a release.
 *
 * Usage:
 *   php scripts/compare-with-ors.php
 *   php scripts/compare-with-ors.php --json > diff.json
 *   php scripts/compare-with-ors.php --url=https://tasnif.joriy.uz/api/regions
 */

const DEFAULT_URL = 'https://tasnif.joriy.uz/api/regions';

$options = getopt('', ['json', 'url::']);
$asJson  = isset($options['json']);
$url     = $options['url'] ?? DEFAULT_URL;

$dataDir = dirname(__DIR__).'/database/data';

$ours   = loadOurs($dataDir);
$theirs = loadOrs($url);

$report = [
  'source'     => $url,
  'regions'    => compare($ours['regions'], $theirs['regions']),
  'districts'  => compare($ours['districts'], $theirs['districts']),
  'quarters'   => ['note' => 'ORS does not expose quarters by SOATO code; compared manually.'],
];

if ($asJson) {
  echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
  exit(0);
}

printReport($report);
exit(hasDifferences($report) ? 1 : 0);

/**
 * Our JSON files, keyed by SOATO code.
 */
function loadOurs(string $dir): array
{
  $read = function (string $file) use ($dir): array {
    $path = $dir.'/'.$file;

    if (!is_file($path)) {
      fail("Data file not found: {$path}");
    }

    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
  };

  $index = function (array $rows) {
    $out = [];

    foreach ($rows as $row) {
      $out[(string) $row['soato_id']] = [
        'soato'  => (string) $row['soato_id'],
        'name'   => $row['name_uz'],
        'ru'     => $row['name_ru'] ?? null,
        'parent' => null,
      ];
    }

    return $out;
  };

  $regions   = $index($read('regions.json'));
  $districts = $index($read('districts.json'));

  // Our districts point at the region by primary key, so translate that into a SOATO code.
  $regionSoatoById = [];
  foreach ($read('regions.json') as $row) {
    $regionSoatoById[$row['id']] = (string) $row['soato_id'];
  }

  foreach ($read('districts.json') as $row) {
    $districts[(string) $row['soato_id']]['parent'] = $regionSoatoById[$row['region_id']] ?? null;
  }

  return ['regions' => $regions, 'districts' => $districts];
}

/**
 * The ORS feed: one flat list where a null parentId marks a region.
 */
function loadOrs(string $url): array
{
  $context = stream_context_create([
    'http' => ['timeout' => 30, 'header' => "Accept: application/json\r\n"],
  ]);

  $body = @file_get_contents($url, false, $context);

  if ($body === false) {
    fail("Could not fetch {$url}");
  }

  $rows = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

  $regions = $districts = [];

  foreach ($rows as $row) {
    $entry = [
      'soato'  => (string) $row['id'],
      'name'   => $row['name']['uz'] ?? null,
      'ru'     => $row['name']['ru'] ?? null,
      'en'     => $row['name']['en'] ?? null,
      'kaa'    => $row['name']['kaa'] ?? null,
      'parent' => isset($row['parentId']) ? (string) $row['parentId'] : null,
      'lat'    => $row['latitude'] ?? null,
      'lng'    => $row['longitude'] ?? null,
    ];

    if ($entry['parent'] === null) {
      $regions[$entry['soato']] = $entry;
    } else {
      $districts[$entry['soato']] = $entry;
    }
  }

  return ['regions' => $regions, 'districts' => $districts];
}

function compare(array $ours, array $theirs): array
{
  $missing = array_diff_key($theirs, $ours);   // they have it, we do not
  $extra   = array_diff_key($ours, $theirs);   // we have it, they do not
  $common  = array_intersect_key($ours, $theirs);

  $renamed = $reparented = $coordinates = [];

  foreach ($common as $soato => $mine) {
    $their = $theirs[$soato];

    if (normalize($mine['name']) !== normalize($their['name'])) {
      $renamed[] = ['soato' => $soato, 'ours' => $mine['name'], 'ors' => $their['name']];
    }

    if ($mine['parent'] !== null && $their['parent'] !== null && $mine['parent'] !== $their['parent']) {
      $reparented[] = ['soato' => $soato, 'name' => $mine['name'], 'ours' => $mine['parent'], 'ors' => $their['parent']];
    }

    if (!empty($their['lat']) && !empty($their['lng'])) {
      $coordinates[$soato] = ['lat' => $their['lat'], 'lng' => $their['lng']];
    }
  }

  return [
    'ours_total'         => count($ours),
    'ors_total'          => count($theirs),
    'matched'            => count($common),
    'missing_from_ours'  => array_values($missing),
    'missing_from_ors'   => array_values($extra),
    'renamed'            => $renamed,
    'reparented'         => $reparented,
    'with_coordinates'   => count($coordinates),
  ];
}

/**
 * Names differ in apostrophe style (' vs ‘ vs ʻ) far more often than in substance.
 */
function normalize(?string $name): string
{
  $name = str_replace(['‘', '’', 'ʻ', 'ʼ', '`', '´'], "'", (string) $name);

  return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
}

function hasDifferences(array $report): bool
{
  foreach (['regions', 'districts'] as $level) {
    $r = $report[$level];

    if ($r['missing_from_ours'] || $r['missing_from_ors'] || $r['renamed'] || $r['reparented']) {
      return true;
    }
  }

  return false;
}

function printReport(array $report): void
{
  echo "Comparing against {$report['source']}", PHP_EOL;

  foreach (['regions' => 'REGIONS', 'districts' => 'DISTRICTS'] as $key => $title) {
    $r = $report[$key];

    echo PHP_EOL, $title, PHP_EOL, str_repeat('=', strlen($title)), PHP_EOL;
    printf("ours: %d   ORS: %d   matched by SOATO: %d%s", $r['ours_total'], $r['ors_total'], $r['matched'], PHP_EOL);

    section('In ORS but not in our data', $r['missing_from_ours'], function (array $i) {
      return sprintf('%-10s %s', $i['soato'], $i['name']);
    });

    section('In our data but not in ORS', $r['missing_from_ors'], function (array $i) {
      return sprintf('%-10s %s', $i['soato'], $i['name']);
    });

    section('Different name', $r['renamed'], function (array $i) {
      return sprintf('%-10s ours: %-32s ORS: %s', $i['soato'], $i['ours'], $i['ors']);
    });

    section('Different parent region', $r['reparented'], function (array $i) {
      return sprintf('%-10s %-32s ours: %s   ORS: %s', $i['soato'], $i['name'], $i['ours'], $i['ors']);
    });

    if ($r['with_coordinates']) {
      printf("%sORS has coordinates for %d of the matched units.%s", PHP_EOL, $r['with_coordinates'], PHP_EOL);
    }
  }

  echo PHP_EOL, hasDifferences($report)
    ? "Differences found — verify each one against the official classifier before releasing."
    : "No differences.", PHP_EOL;
}

function section(string $title, array $items, callable $format): void
{
  if (!$items) {
    return;
  }

  printf('%s%s (%d):%s', PHP_EOL, $title, count($items), PHP_EOL);

  foreach ($items as $item) {
    echo '  ', $format($item), PHP_EOL;
  }
}

function fail(string $message): never
{
  fwrite(STDERR, $message.PHP_EOL);
  exit(2);
}
