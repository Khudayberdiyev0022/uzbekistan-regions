<?php

namespace Khudayberdiyev\UzbekistanRegions;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Khudayberdiyev\UzbekistanRegions\Console\SeedCommand;

class UzbekistanRegionsServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    $this->mergeConfigFrom(__DIR__.'/../config/uzbekistan-regions.php', 'uzbekistan-regions');
  }

  public function boot(): void
  {
    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    $this->registerRoutes();

    if ($this->app->runningInConsole()) {
      $this->commands([SeedCommand::class]);

      $this->publishes([
        __DIR__.'/../config/uzbekistan-regions.php' => config_path('uzbekistan-regions.php'),
      ], 'uzbekistan-regions-config');

      $this->publishes([
        __DIR__.'/../database/migrations' => database_path('migrations'),
      ], 'uzbekistan-regions-migrations');

      $this->publishes([
        __DIR__.'/../database/data' => database_path('data/uzbekistan-regions'),
      ], 'uzbekistan-regions-data');
    }
  }

  protected function registerRoutes(): void
  {
    if (!config('uzbekistan-regions.routes.enabled', true)) {
      return;
    }

    Route::group([
      'prefix'     => config('uzbekistan-regions.routes.prefix', 'api/v1'),
      'middleware' => config('uzbekistan-regions.routes.middleware', ['api']),
    ], function () {
      $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    });
  }
}
