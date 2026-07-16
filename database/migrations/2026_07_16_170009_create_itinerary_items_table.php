<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('itinerary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained()->cascadeOnDelete();
            $table->integer('day_number');
            $table->time('schedule_time');
            $table->enum('type', ['hotel', 'restaurant', 'attraction', 'transport', 'shopping', 'other']);
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('location', 255)->nullable();
            $table->bigInteger('estimated_cost')->default(0);
            $table->boolean('is_bookable')->default(false);
            $table->foreignId('merchant_id')->nullable()->constrained('merchants')->nullOnDelete();
            $table->bigInteger('booking_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itinerary_items');
    }
};
