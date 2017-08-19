<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $guarded = [];

    protected $table = 'history';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
