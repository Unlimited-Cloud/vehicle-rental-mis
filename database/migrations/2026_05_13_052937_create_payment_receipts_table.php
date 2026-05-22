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
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->decimal('total_invoice_amount', 15, 2);
            $table->decimal('tds_deduction', 15, 2)->default(0);
            $table->decimal('tds_rate', 5, 2)->default(1.5);
            $table->decimal('net_paid_amount', 15, 2);
            $table->string('payment_method'); // cash, bank, cheque, wallet
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('wallet_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('transaction_id')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->boolean('tds_applied')->default(false);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
