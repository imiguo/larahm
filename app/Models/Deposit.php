<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $guarded = [];

    protected $table = 'deposits';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
