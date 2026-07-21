<?php

namespace Khudayberdiyev\UzbekistanRegions\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

/**
 * A fresh install never creates the columns v1.2.0 removed, so the migration that removes
 * them is only exercised by an upgrade. These tests rebuild the old shape and upgrade it.
 */
class UpgradeTest extends TestCase
{
  use RefreshDatabase;

  public function test_it_drops_a_legacy_order_column_together_with_its_index(): void
  {
    foreach (['regions', 'districts', 'quarters'] as $table) {
      Schema::table($table, function (Blueprint $blueprint) use ($table) {
        $blueprint->integer('order')->default(1);
        $blueprint->index('order', "idx_{$table}_order");
      });

      $this->assertTrue(Schema::hasColumn($table, 'order'));
    }

    $this->migration()->up();

    foreach (['regions', 'districts', 'quarters'] as $table) {
      $this->assertFalse(Schema::hasColumn($table, 'order'), "{$table}.order survived");
    }
  }

  public function test_it_skips_tables_that_never_had_the_column(): void
  {
    $this->migration()->up();

    $this->assertFalse(Schema::hasColumn('regions', 'order'));
  }

  protected function migration(): object
  {
    return require __DIR__.'/../database/migrations/2026_07_21_000001_drop_order_from_soato_tables.php';
  }
}
