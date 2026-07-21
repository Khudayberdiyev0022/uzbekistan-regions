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
    Schema::create('districts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
      $table->string('soato_id', 10)->unique();
      $table->string('name_uz');
      $table->string('name_oz');
      $table->string('name_ru')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('districts');
  }
};
