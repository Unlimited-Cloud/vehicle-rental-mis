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
        Schema::table('khalti_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('crew_id')->nullable();
            $table->string('payment_type')->nullable()->after('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khalti_payments', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_id',
                'payment_id',
                'crew_id',
                'payment_type',
            ]);
        });
    }
};
