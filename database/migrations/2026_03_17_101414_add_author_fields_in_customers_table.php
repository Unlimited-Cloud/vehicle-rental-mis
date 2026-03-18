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
            $table->string('customer_uuid')->nullable()->after('id');
            $table->string('customer_type')->default('institution')->after('customer_uuid');
            $table->string('author_type')->nullable()->after('created_at');
            $table->unsignedBigInteger('author_id')->nullable()->after('author_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'customer_uuid',
                'customer_type',
                'author_type',
                'author_id'
            ]);
        });
    }
};
