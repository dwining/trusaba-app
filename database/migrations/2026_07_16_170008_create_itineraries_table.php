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
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('destination', 200);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days');
            $table->integer('total_participants')->default(1);
            $table->bigInteger('budget_input')->nullable();
            $table->bigInteger('estimated_budget')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->longText('ai_raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itineraries');
    }
};
