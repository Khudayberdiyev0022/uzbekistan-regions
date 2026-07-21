<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function getConnection(): ?string
  {
    return config('uzbekistan-regions.connection');
  }

  public function up(): void
  {
    Schema::table('regions', function (Blueprint $table) {
      $table->string('type', 20)->default('region')->after('soato_id')->index();
    });

    Schema::table('districts', function (Blueprint $table) {
      $table->string('type', 20)->default('district')->after('soato_id')->index();
    });
  }

  public function down(): void
  {
    Schema::table('regions', fn (Blueprint $table) => $table->dropColumn('type'));
    Schema::table('districts', fn (Blueprint $table) => $table->dropColumn('type'));
  }
};
