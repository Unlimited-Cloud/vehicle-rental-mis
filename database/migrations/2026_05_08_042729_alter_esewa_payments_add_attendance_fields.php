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
        Schema::table('esewa_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_id')->nullable()->after('booking_id');
            $table->unsignedBigInteger('crew_id')->nullable()->after('attendance_id');
            $table->unsignedBigInteger('payment_id')->nullable()->after('crew_id');
            $table->string('payment_type')->nullable()->after('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('esewa_payments', function (Blueprint $table) {
            $table->dropColumn(['attendance_id', 'crew_id', 'payment_id', 'payment_type']);
        });
    }
};
