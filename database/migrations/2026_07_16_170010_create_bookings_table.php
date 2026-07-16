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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('itinerary_id')->nullable()->constrained('itineraries')->nullOnDelete();
            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();
            $table->foreignId('itinerary_item_id')->nullable()->constrained('itinerary_items')->nullOnDelete();
            $table->enum('booking_type', ['hotel', 'restaurant', 'attraction', 'transport', 'other']);
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->date('booking_date')->nullable();
            $table->integer('quantity')->default(1);
            $table->json('resource_detail')->nullable();
            $table->bigInteger('amount');
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'completed', 'cancelled'])->default('pending');
            $table->string('voucher_code', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
