<?php

use Khudayberdiyev\UzbekistanRegions\Http\Middleware\SetLocale;

return [

  /*
  |--------------------------------------------------------------------------
  | Routes
  |--------------------------------------------------------------------------
  |
  | The package ships a read-only REST API. Set "enabled" to false if you
  | only need the models, migrations and the seeder.
  |
  */

  'routes' => [
    'enabled'    => true,
    'prefix'     => 'api/v1',
    'middleware' => ['api', SetLocale::class],
  ],

  /*
  |--------------------------------------------------------------------------
  | Localization
  |--------------------------------------------------------------------------
  |
  | Every name is stored in three columns: name_uz, name_oz, name_ru.
  | The locale is resolved from the Accept-Language header by SetLocale.
  |
  */

  'locales'        => ['uz', 'oz', 'ru'],
  'default_locale' => 'uz',

  /*
  |--------------------------------------------------------------------------
  | Database
  |--------------------------------------------------------------------------
  |
  | "connection" null means the application default connection.
  | "data_path" null means the JSON files shipped with the package.
  |
  */

  'connection' => null,
  'data_path'  => null,

];
