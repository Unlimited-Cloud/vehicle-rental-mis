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
        Schema::table('crew_bank_details', function (Blueprint $table) {
            $table->tinyInteger('is_verified')
                ->nullable()
                ->comment('NULL = pending, 0 = failed, 1 = verified')
                ->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_bank_details', function (Blueprint $table) {
            $table->dropColumn('is_verified');
        });
    }
};
