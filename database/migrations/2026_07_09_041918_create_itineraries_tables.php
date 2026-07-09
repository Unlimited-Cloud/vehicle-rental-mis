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
            $table->foreignId('booking_id')->constrained('vehicle_bookings')->onDelete('cascade');
            $table->string('file_no')->nullable()->index();
            $table->unsignedInteger('day_number')->nullable();
            $table->date('itinerary_date')->nullable();
            $table->string('from_destination')->nullable();
            $table->string('to_destination')->nullable();
            $table->decimal('est_km', 10, 2)->default(0);
            $table->decimal('est_hours', 10, 2)->default(0);
            $table->boolean('is_overnight')->default(false);
            $table->decimal('per_km_rate', 10, 2)->default(0);
            $table->decimal('per_hour_rate', 10, 2)->default(0);
            $table->decimal('overnight_charge', 10, 2)->default(0);
            $table->decimal('est_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itineraries_tables');
    }
};
