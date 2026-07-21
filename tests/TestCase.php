<?php

namespace Khudayberdiyev\UzbekistanRegions\Tests;

use Khudayberdiyev\UzbekistanRegions\UzbekistanRegionsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
  protected function getPackageProviders($app): array
  {
    return [UzbekistanRegionsServiceProvider::class];
  }

  /**
   * SQLite by default. CI runs the suite against PostgreSQL as well, because the search
   * scope takes a different path there — ILIKE instead of the cased LIKE variants.
   */
  protected function defineEnvironment($app): void
  {
    if (env('DB_CONNECTION') !== 'pgsql') {
      $app['config']->set('database.default', 'testing');

      return;
    }

    $app['config']->set('database.default', 'pgsql');
    $app['config']->set('database.connections.pgsql', [
      'driver'   => 'pgsql',
      'host'     => env('DB_HOST', '127.0.0.1'),
      'port'     => env('DB_PORT', '5432'),
      'database' => env('DB_DATABASE', 'testing'),
      'username' => env('DB_USERNAME', 'postgres'),
      'password' => env('DB_PASSWORD', 'postgres'),
      'charset'  => 'utf8',
      'prefix'   => '',
      'search_path' => 'public',
    ]);
  }
}
