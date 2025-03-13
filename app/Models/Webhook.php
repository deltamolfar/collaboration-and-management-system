<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'action',
        'url',
        'headers',
    ];

    protected $casts = [
        'headers' => 'array',
    ];

    static public function execute($action, $data)
    {
        $webhooks = Webhook::where('action', $action)->get();

        foreach ($webhooks as $webhook) {
            $client = new \GuzzleHttp\Client();
            $client->post($webhook->url, [
                'headers' => $webhook->headers,
                'json' => $data,
            ]);
        }
    }
}
