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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->string('sensor_type'); // temperature, humidity, accelerometer, etc.
            $table->decimal('value', 10, 2)->nullable(); // Main sensor value
            $table->string('unit', 10)->nullable(); // °C, %, g, etc.
            $table->decimal('x_axis', 10, 4)->nullable(); // For accelerometer/gyroscope
            $table->decimal('y_axis', 10, 4)->nullable(); // For accelerometer/gyroscope  
            $table->decimal('z_axis', 10, 4)->nullable(); // For accelerometer/gyroscope
            $table->json('metadata')->nullable(); // Additional sensor data
            $table->timestamps();

            // Index for better query performance
            $table->index(['device_id', 'sensor_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
