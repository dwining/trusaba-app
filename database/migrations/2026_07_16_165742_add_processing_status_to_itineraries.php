<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('itineraries')) {
            return;
        }

        DB::statement("ALTER TABLE itineraries MODIFY COLUMN status ENUM('draft','processing','confirmed','ongoing','completed','cancelled','failed') NOT NULL DEFAULT 'processing'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('itineraries')) {
            return;
        }

        DB::statement("ALTER TABLE itineraries MODIFY COLUMN status ENUM('draft','confirmed','ongoing','completed','cancelled') NOT NULL DEFAULT 'draft'");
    }
};
