<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeDevice extends Model
{
    protected $fillable = ['name', 'type', 'state'];
}
