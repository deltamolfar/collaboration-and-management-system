<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'webhook_id', 'action', 'payload', 'response', 'status_code'
    ];
}
