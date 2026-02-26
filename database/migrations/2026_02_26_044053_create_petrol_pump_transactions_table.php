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
        Schema::create('petrol_pump_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petrol_pump_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['credit', 'debit', 'payment', 'payable'])->default('credit');
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('fuel_quantity', 10, 2)->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'cng', 'other'])->nullable();
            $table->decimal('rate_per_liter', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petrol_pump_transactions');
    }
};
