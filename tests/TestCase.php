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

  protected function defineEnvironment($app): void
  {
    $app['config']->set('database.default', 'testing');
  }
}
