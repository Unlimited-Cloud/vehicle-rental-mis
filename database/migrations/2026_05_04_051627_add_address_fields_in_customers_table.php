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
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('country_id')->nullable()->after('phone');
            $table->integer('district_id')->nullable()->after('state');
            $table->integer('vdc_id')->nullable()->after('district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'country_id',
                'district_id',
                'vdc_id',
            ]);
        });
    }
};
