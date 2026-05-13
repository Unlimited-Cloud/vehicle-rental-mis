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
        Schema::create('payment_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_receipt_id');
            $table->unsignedBigInteger('vehicle_receipt_id');
            $table->string('invoice_number');
            $table->decimal('invoice_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_receipt_items');
    }
};
