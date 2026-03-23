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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crew_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('vehicle_moment_id')->nullable();

            $table->date('attendance_date');

            $table->decimal('salary_amount', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deduction', 10, 2)->default(0);

            $table->string('status')->default('present'); // present, absent, leave
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
