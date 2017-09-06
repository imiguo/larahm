<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitlog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    protected $table = 'visit';
}
