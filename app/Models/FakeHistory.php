<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakeHistory extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(FakeUser::class);
    }
}
