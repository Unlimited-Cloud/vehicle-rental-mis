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
        Schema::create('vehicle_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->string('from_destination')->nullable();
            $table->string('to_destination')->nullable();
            $table->string('no_of_people')->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('total_amount', 10, 2)->nullable();

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                ->default('confirmed');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_bookings');
    }
};
