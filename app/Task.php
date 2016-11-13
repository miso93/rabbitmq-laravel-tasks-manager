<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    protected $fillable = ['body', 'priority', 'done'];

    protected $casts = [
        'priority' => 'integer',
        'done'     => 'boolean'
    ];
}
