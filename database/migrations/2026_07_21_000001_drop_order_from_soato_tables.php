<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The column never held anything but its default of 1: the classifier does not publish an
 * ordering, so sorting by it did nothing while pretending to work.
 */
return new class extends Migration {
  public function getConnection(): ?string
  {
    return config('uzbekistan-regions.connection');
  }

  public function up(): void
  {
    foreach (['regions', 'districts', 'quarters'] as $table) {
      if (!Schema::hasColumn($table, 'order')) {
        continue;
      }

      // SQLite refuses to drop a column an index still points at.
      $index = "idx_{$table}_order";

      if (collect(Schema::getIndexes($table))->contains(fn (array $i) => $i['name'] === $index)) {
        Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
      }

      Schema::table($table, fn (Blueprint $t) => $t->dropColumn('order'));
    }
  }

  public function down(): void
  {
    foreach (['regions', 'districts', 'quarters'] as $table) {
      if (!Schema::hasColumn($table, 'order')) {
        Schema::table($table, fn (Blueprint $t) => $t->integer('order')->default(1));
      }
    }
  }
};
