<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookLogController extends Controller
{
    public function show(Webhook $webhook)
    {
        return response()->json($webhook->logs()->latest()->take(20)->get());
    }
}
