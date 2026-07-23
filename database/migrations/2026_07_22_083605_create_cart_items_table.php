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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('itinerary_id')->constrained('itineraries')->cascadeOnDelete();
            $table->foreignId('itinerary_item_id')->nullable()->constrained('itinerary_items')->nullOnDelete();
            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();
            $table->string('booking_type'); // hotel|restaurant|attraction|transport|other
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->json('resource_detail')->nullable(); // {"room_type":"Deluxe","nights":2}
            $table->bigInteger('amount')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'itinerary_item_id']); // prevent duplicate
            $table->index(['user_id', 'itinerary_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
