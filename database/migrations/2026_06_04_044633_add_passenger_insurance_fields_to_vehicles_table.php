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
            $table->boolean('passenger_insured')->default(0)->after('insurance_policy_document');
            $table->decimal('passenger_insured_amount', 15, 2)
                ->nullable()
                ->after('passenger_insured');
            $table->string('passenger_insurance_company')
                ->nullable()
                ->after('passenger_insured_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'passenger_insured',
                'passenger_insured_amount',
                'passenger_insurance_company',
            ]);
        });
    }
};
