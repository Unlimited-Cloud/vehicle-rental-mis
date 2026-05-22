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
        Schema::table('basic_tables', function (Blueprint $table) {
            $table->decimal('tds', 8, 2)->default(0)->after('company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_tables', function (Blueprint $table) {
            $table->dropColumn('tds');
        });
    }
};
