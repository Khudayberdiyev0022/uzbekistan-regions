<?php

namespace Khudayberdiyev\UzbekistanRegions\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Khudayberdiyev\UzbekistanRegions\Models\District;
use Khudayberdiyev\UzbekistanRegions\Models\Quarter;
use Khudayberdiyev\UzbekistanRegions\Models\Region;

class ApiTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    $this->artisan('uzbekistan-regions:seed')->assertSuccessful();
  }

  public function test_it_seeds_the_soato_dataset(): void
  {
    $this->assertSame(14, Region::count());
    $this->assertSame(210, District::count());
    $this->assertSame(2641, Quarter::count());
  }

  public function test_it_lists_regions(): void
  {
    $this->getJson('/api/v1/regions')
      ->assertOk()
      ->assertJsonCount(14, 'data')
      ->assertJsonStructure(['data' => [['id', 'soato_id', 'name', 'districts_count', 'quarters_count']]]);
  }

  public function test_it_returns_names_in_the_requested_locale(): void
  {
    $region = Region::first();

    $this->getJson('/api/v1/regions/'.$region->id, ['Accept-Language' => 'ru'])
      ->assertOk()
      ->assertJsonPath('data.name', $region->name_ru);
  }

  public function test_it_searches_case_insensitively(): void
  {
    $this->getJson('/api/v1/regions?search=andijon')
      ->assertOk()
      ->assertJsonPath('data.0.name', 'Andijon viloyati');
  }

  public function test_it_sorts_by_the_localized_name(): void
  {
    $expected = Region::orderBy('name_uz')->value('name_uz');

    $this->getJson('/api/v1/regions?sort=name&order=asc')
      ->assertOk()
      ->assertJsonPath('data.0.name', $expected);
  }

  public function test_it_filters_districts_by_region(): void
  {
    $region = Region::withCount('districts')->first();

    $this->getJson('/api/v1/districts?region_id='.$region->id)
      ->assertOk()
      ->assertJsonCount($region->districts_count, 'data');
  }

  public function test_it_paginates_quarters(): void
  {
    $this->getJson('/api/v1/quarters?per_page=25')
      ->assertOk()
      ->assertJsonCount(25, 'data')
      ->assertJsonPath('meta.total', 2641);
  }

  public function test_it_rejects_an_unknown_sort_column(): void
  {
    $this->getJson('/api/v1/regions?sort=password')
      ->assertStatus(422);
  }

  public function test_it_404s_for_a_missing_region(): void
  {
    $this->getJson('/api/v1/regions/999999')->assertNotFound();
  }
}
