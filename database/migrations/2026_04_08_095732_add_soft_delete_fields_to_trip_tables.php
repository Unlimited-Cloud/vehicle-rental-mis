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
        Schema::table('trip_tables', function (Blueprint $table) {
            // trip_categories table
            Schema::table('trip_categories', function (Blueprint $table) {
                $table->softDeletes(); // adds deleted_at
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
            });

            // trip_routes table
            Schema::table('trip_routes', function (Blueprint $table) {
                $table->softDeletes(); // adds deleted_at
                $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // trip_categories table rollback
        Schema::table('trip_categories', function (Blueprint $table) {
            $table->dropColumn(['deleted_at', 'deleted_by']);
        });

        // trip_routes table rollback
        Schema::table('trip_routes', function (Blueprint $table) {
            $table->dropColumn(['deleted_at', 'deleted_by']);
        });
    }
};
