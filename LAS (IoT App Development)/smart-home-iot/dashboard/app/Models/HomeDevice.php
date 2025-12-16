<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeDevice extends Model
{
    protected $fillable = ['name', 'type', 'state'];

    protected static function booted()
    {
        static::updated(function ($device) {
            // Check if state changed to 1 (Open) and it is a door sensor
            if ($device->isDirty('state') && $device->state == 1 && str_contains(strtolower($device->type), 'door')) {
                // Send notification to all users
                $users = \App\Models\User::all();
                foreach ($users as $user) {
                    $user->notify(new \App\Notifications\DoorOpenNotification($device->name));
                }
            }
        });
    }
}
