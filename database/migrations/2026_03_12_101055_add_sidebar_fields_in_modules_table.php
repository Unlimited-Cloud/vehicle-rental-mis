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
        Schema::table('modules', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('name');
            $table->string('route')->nullable()->after('icon');
            $table->string('permission')->nullable()->after('route');
            $table->unsignedBigInteger('parent_id')->nullable()->after('permission');
            $table->unsignedBigInteger('order_by')->default(1)->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'icon',
                'route',
                'permission',
                'parent_id',
                'order_by'
            ]);
        });
    }
};
