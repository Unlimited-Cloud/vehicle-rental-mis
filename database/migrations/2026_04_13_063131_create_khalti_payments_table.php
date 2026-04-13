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
        Schema::create('khalti_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('merchant_transaction_id')->nullable();
            $table->string('pidx');
            $table->string('txn_id')->nullable();

            $table->decimal('amount', 10, 2);
            $table->decimal('fees', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();

            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_mobile')->nullable();

            $table->string('status');
            $table->text('khalti_init_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khalti_payments');
    }
};
