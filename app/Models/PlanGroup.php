<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanGroup extends Model
{
    protected $guarded = [];

    protected $table = 'types';

    public function plans()
    {
        return $this->hasMany(Plan::class, 'parent');
    }

    public function period()
    {
        $list = [
            'd' => 'daily',
            'w' => 'weekly',
            'm' => 'monthly',
            'y' => 'yearly',
            'end' => 'after',
        ];
        return $list[$this->period];
    }
}
