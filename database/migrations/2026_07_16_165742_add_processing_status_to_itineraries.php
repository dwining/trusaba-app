<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE itineraries MODIFY COLUMN status ENUM('draft','processing','confirmed','ongoing','completed','cancelled','failed') NOT NULL DEFAULT 'processing'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE itineraries MODIFY COLUMN status ENUM('draft','confirmed','ongoing','completed','cancelled') NOT NULL DEFAULT 'draft'");
    }
};
