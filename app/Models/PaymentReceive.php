<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceive extends Model
{
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];
}
