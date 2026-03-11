<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Booking reference
            $table->unsignedBigInteger('vehicle_booking_id');

            // Payment amount
            $table->decimal('amount', 10, 2);

            // Payment method
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'card',
                'online',
                'cheque'
            ]);

            // Transaction reference (optional)
            $table->string('transaction_reference')->nullable();

            // Payment date
            $table->datetime('payment_date');

            // Notes
            $table->text('notes')->nullable();

            // User who recorded payment
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->datetime('deleted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
