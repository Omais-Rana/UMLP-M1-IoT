<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'arduino_nano', 'esp32'
        'mac_address',
        'ip_address',
        'status', // 'online', 'offline'
        'last_seen_at',
        'location',
        'description',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function latestReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class)->latest('created_at')->limit(10);
    }

    public function isOnline(): bool
    {
        return $this->status === 'online' &&
            $this->last_seen_at &&
            $this->last_seen_at->diffInMinutes(now()) < 5;
    }
}
