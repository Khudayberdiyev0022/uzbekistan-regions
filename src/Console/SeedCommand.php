<?php

namespace Khudayberdiyev\UzbekistanRegions\Console;

use Illuminate\Console\Command;
use Khudayberdiyev\UzbekistanRegions\Database\Seeders\UzbekistanRegionsSeeder;

class SeedCommand extends Command
{
  protected $signature = 'uzbekistan-regions:seed
                          {--force : Run in production without confirmation}';

  protected $description = 'Fill the regions, districts and quarters tables with the SOATO dataset';

  public function handle(): int
  {
    if ($this->getLaravel()->environment('production')
      && !$this->option('force')
      && !$this->confirm('This replaces all rows in regions, districts and quarters. Continue?')) {
      $this->comment('Aborted.');

      return self::FAILURE;
    }

    $this->call('db:seed', [
      '--class' => UzbekistanRegionsSeeder::class,
      '--force' => true,
    ]);

    return self::SUCCESS;
  }
}
