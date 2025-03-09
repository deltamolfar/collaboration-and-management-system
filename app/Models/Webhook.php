<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'action',
        'url',
        'header_key',
        'header_value',
    ];
}
