<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    const TYPE_DEPOSIT = 1;
    const TYPE_WITHDRAW = 2;

    const PS_PERFECTMONEY = 1;
    const PS_PAYEER = 2;
    const PS_BITCOIN = 3;

    const STATUS_START = 1;
    const STATUS_OK = 2;
    const STATUS_CANCEL = 3;

    protected $casts = [
        'data' => 'array',
    ];
}
