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
        Schema::create('esewa_payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_uuid');
            $table->decimal('amount', 10, 2);
            $table->string('status');

            $table->unsignedBigInteger('booking_id');

            $table->text('esewa_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esewa_payments');
    }
};
