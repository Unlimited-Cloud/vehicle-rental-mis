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
            $table->longText('terms_and_conditions')->nullable();
            $table->longText('privacy_policy')->nullable();
            $table->longText('about_us')->nullable();
            $table->longText('contact_us')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_tables', function (Blueprint $table) {
            $table->dropColumn([
                'terms_and_conditions',
                'privacy_policy',
                'about_us',
                'contact_us',
            ]);
        });
    }
};
