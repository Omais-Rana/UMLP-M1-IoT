<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'sensor_type', // 'temperature', 'humidity', 'accelerometer', 'gyroscope', etc.
        'value',
        'unit',
        'x_axis', // for accelerometer/gyroscope
        'y_axis', // for accelerometer/gyroscope
        'z_axis', // for accelerometer/gyroscope
        'metadata', // JSON field for additional data
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'x_axis' => 'decimal:2',
        'y_axis' => 'decimal:2',
        'z_axis' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    // Scope for filtering by sensor type
    public function scopeBySensorType($query, string $sensorType)
    {
        return $query->where('sensor_type', $sensorType);
    }

    // Scope for recent readings
    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}
