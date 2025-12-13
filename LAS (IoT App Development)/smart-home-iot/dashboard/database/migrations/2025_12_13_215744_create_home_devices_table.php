<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('home_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');   // e.g., "Front Door"
            $table->string('type');   // e.g., "sensor", "switch"
            $table->boolean('state')->default(0); // 0 = Closed, 1 = Open
            $table->timestamps();
        });

        // Insert the default "Front Door" record immediately
        DB::table('home_devices')->insert([
            'name' => 'Front Door',
            'type' => 'door_sensor',
            'state' => 0, // Closed by default
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
