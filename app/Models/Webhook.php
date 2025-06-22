<?php

namespace App\Models;

use Error;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Webhook extends Model
{
    protected $fillable = [
        'action',
        'url',
        'active',
        'headers',
        'hmac_secret',
    ];

    protected $casts = [
        'action' => 'string',
        'url' => 'string',
        'active' => 'boolean',
        'headers' => 'array',
        'hmac_secret' => 'string',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    public static function execute($action, $data)
    {
        Log::info("Executing webhook for action: {$action}", $data);
        $webhooks = Webhook::where('action', $action)->get();

        foreach ($webhooks as $webhook) {
            if (!$webhook->active) continue;

            $headers = [];
            if (is_array($webhook->headers)) {
                foreach ($webhook->headers as $header) {
                    $headers[$header['key']] = $header['value'];
                }
            }
            if ($webhook->hmac_secret) {
                $headers['X-Hub-Signature'] = 'sha256=' . hash_hmac('sha256', json_encode($data), $webhook->hmac_secret);
            }
            $client = new \GuzzleHttp\Client();
            try {
                $res = $client->post($webhook->url, [
                    'headers' => $headers,
                    'json' => $data,
                ]);

                Log::info("Webhook executed successfully for action: {$action}", [
                    'action' => $action,
                    'webhook_id' => $webhook->id,
                    'payload' => json_encode($data),
                    'response' => $res->getBody()->getContents(),
                    'status_code' => $res->getStatusCode(),
                ]);

                WebhookLog::create([
                    'action' => $action,
                    'webhook_id' => $webhook->id,
                    'payload' => json_encode($data),
                    'response' => $res->getBody()->getContents(),
                    'status_code' => $res->getStatusCode(),
                ]);
            } catch (\Exception $e) {
                Log::error("Webhook execution failed for action: {$action}", [
                    'action' => $action,
                    'webhook_id' => $webhook->id,
                    'payload' => json_encode($data),
                    'response' => $e->getMessage(),
                ]);
            }
        }
    }
}
