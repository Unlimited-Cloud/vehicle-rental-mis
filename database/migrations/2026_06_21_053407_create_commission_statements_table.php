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
        Schema::create('commission_statements', function (Blueprint $table) {
            $table->id();
            $table->string('statement_number')->unique();

            $table->string('payee_type')->nullable();   // 'agent' or 'owner'
            $table->string('payee_code')->nullable();   // agent_code, or owner id/code
            $table->unsignedBigInteger('payee_id')->nullable(); // FK to agents.id or owners.id (polymorphic-lite)

            // Link back to the payment that triggered this statement
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('vehicle_booking_id')->nullable();

            // Statement period (useful if you ever batch multiple bookings into one statement)
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            // Financial snapshot at time of statement generation
            $table->decimal('booking_amount', 12, 2)->default(0);   // commission base
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('tds_rate', 5, 2)->default(0);
            $table->decimal('tds_amount', 12, 2)->default(0);
            $table->decimal('net_paid_amount', 12, 2)->default(0);

            $table->string('payment_method')->nullable(); // bank_transfer, wallet, etc.
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('transaction_reference')->nullable();

            $table->date('payment_date')->nullable();
            $table->string('pdf_path')->nullable();

            $table->enum('status', ['generated', 'sent', 'acknowledged'])->default('generated');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_statements');
    }
};
