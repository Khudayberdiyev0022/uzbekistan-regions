<?php

namespace Khudayberdiyev\UzbekistanRegions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UzbekistanRegionsSeeder extends Seeder
{
  /**
   * Rows inserted per query.
   */
  protected int $chunkSize = 500;

  public function run(): void
  {
    $connection = DB::connection(config('uzbekistan-regions.connection'));
    $now        = now();

    foreach (['regions', 'districts', 'quarters'] as $table) {
      $rows = $this->readJson($table);

      $connection->table($table)->delete();

      foreach (array_chunk($rows, $this->chunkSize) as $chunk) {
        $connection->table($table)->insert(array_map(
          fn (array $row) => $this->normalize($table, $row, $now),
          $chunk
        ));
      }

      $this->command?->info(sprintf('%s: %d rows seeded.', $table, count($rows)));
    }

    $this->resetSequences($connection);
  }

  protected function dataPath(): string
  {
    return rtrim(
      config('uzbekistan-regions.data_path') ?: __DIR__.'/../data',
      DIRECTORY_SEPARATOR
    );
  }

  protected function readJson(string $table): array
  {
    $path = $this->dataPath().DIRECTORY_SEPARATOR.$table.'.json';

    if (!is_file($path)) {
      throw new RuntimeException("Data file not found: {$path}");
    }

    $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($rows)) {
      throw new RuntimeException("Invalid JSON in: {$path}");
    }

    return $rows;
  }

  protected function normalize(string $table, array $row, $now): array
  {
    $data = [
      'id'         => $row['id'],
      'soato_id'   => (string) $row['soato_id'],
      'name_uz'    => $row['name_uz'],
      'name_oz'    => $row['name_oz'],
      'name_ru'    => $row['name_ru'] ?? null,
      'created_at' => $now,
      'updated_at' => $now,
    ];

    // Quarters are all of one kind, so only regions and districts carry a type.
    if (isset($row['type'])) {
      $data['type'] = $row['type'];
    }

    if ($table === 'districts') {
      $data['region_id'] = $row['region_id'];
    }

    if ($table === 'quarters') {
      $data['district_id'] = $row['district_id'];
    }

    return $data;
  }

  /**
   * Explicit ids were inserted, so PostgreSQL sequences have to catch up.
   */
  protected function resetSequences($connection): void
  {
    if ($connection->getDriverName() !== 'pgsql') {
      return;
    }

    foreach (['regions', 'districts', 'quarters'] as $table) {
      $connection->statement(
        "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), true)"
      );
    }
  }
}
