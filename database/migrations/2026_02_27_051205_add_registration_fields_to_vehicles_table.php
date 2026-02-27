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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('registration_number')->nullable();
            $table->string('registered_at')->nullable();
            $table->enum('number_plate_color', ['RED', 'BLACK', 'GREEN'])->nullable();
            $table->date('registration_expiry')->nullable();
            $table->string('bill_book_number')->nullable();
            $table->string('bill_book_image')->nullable();

            // Insurance Basic Info
            $table->string('insurance_policy_no')->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('insurance_type')->nullable();
            $table->date('insurance_till')->nullable();
            $table->decimal('insurance_cost_per_annum', 10, 2)->nullable();
            $table->string('insurance_policy_document')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            //
        });
    }
};
