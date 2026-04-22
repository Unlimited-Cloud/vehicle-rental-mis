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
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->renameColumn('customer_name', 'contact_person');
            $table->renameColumn('customer_email', 'contact_email');
            $table->renameColumn('customer_phone', 'contact_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->renameColumn('contact_person', 'customer_name');
            $table->renameColumn('contact_email', 'customer_email');
            $table->renameColumn('contact_number', 'customer_phone');
        });
    }
};
