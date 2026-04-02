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
        Schema::create('estimate_bills', function (Blueprint $table) {
            $table->id();
            $table->string('estimate_number')->unique();
            $table->decimal('rate_per_day', 10, 2)->nullable();
            $table->decimal('sub_total', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);

            $table->integer('version')->default(1);
            $table->string('pdf_path')->nullable();

            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('file_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_bills');
    }
};
