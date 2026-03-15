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
        Schema::create('trip_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');

            $table->integer('km')->nullable();

            $table->decimal('car_price', 10, 2)->nullable();
            $table->decimal('hiace_price', 10, 2)->nullable();
            $table->decimal('coaster_price', 10, 2)->nullable();
            $table->decimal('bus_price', 10, 2)->nullable();
            $table->decimal('other_price', 10, 2)->nullable();

            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_routes');
    }
};
