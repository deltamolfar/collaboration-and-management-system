<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebhookController extends Controller
{
    public function index()
    {
        // For API requests, return JSON data
        if (request()->expectsJson())
            return Webhook::latest()->get();
        
        // For web requests, render the page
        return Inertia::render('Admin/Webhooks');
    }

    public function create()
    {
        return Inertia::render('Admin/WebhookCreate');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|string|in:task.create,task.update,task.delete,task.comment,task.log,project.create,project.update,project.delete,role.create,role.update,role.delete,user.create,user.update,user.delete',
            'url' => 'required|url',
            'headers' => 'nullable|array',
            'headers.*.key' => 'required_with:headers|string',
            'headers.*.value' => 'required_with:headers|string',
            'hmac_secret' => 'nullable|string',
        ]);

        Webhook::create($data);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook created successfully.');
    }
    
    public function update(Request $request, Webhook $webhook)
    {
        $data = $request->validate([
            'action' => 'required|string|in:task.create,task.update,task.delete,task.comment,task.log,project.create,project.update,project.delete,role.create,role.update,role.delete,user.create,user.update,user.delete',
            'url' => 'required|url',
            'headers' => 'nullable|array',
            'headers.*.key' => 'required_with:headers|string',
            'headers.*.value' => 'required_with:headers|string',
            'hmac_secret' => 'nullable|string',
        ]);

        $webhook->update($data);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook updated successfully.');
    }
    
    public function destroy(Webhook $webhook)
    {
        $webhook->delete();
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook deleted successfully.');
    }

    public function toggleEnabled(Webhook $webhook)
    {
        $webhook->enabled = !$webhook->enabled;
        $webhook->save();

        return $webhook->enabled;
    }

    public function test(Webhook $webhook)
    {
        $testPayload = [
            'test' => true,
            'timestamp' => now()->toIso8601String(),
            'webhook_id' => $webhook->id,
            'event' => $webhook->action,
            'message' => 'This is a test webhook payload.',
        ];

        $headers = [];
        if (is_array($webhook->headers)) {
            foreach ($webhook->headers as $header) {
                if (!empty($header['key'])) {
                    $headers[$header['key']] = $header['value'];
                }
            }
        }
        if ($webhook->hmac_secret) {
            $headers['X-Hub-Signature'] = 'sha256=' . hash_hmac('sha256', json_encode($testPayload), $webhook->hmac_secret);
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->post($webhook->url, [
                'headers' => $headers,
                'json' => $testPayload,
                'timeout' => 5,
            ]);
            return response()->json([
                'success' => true,
                'status' => $res->getStatusCode(),
                'response' => (string) $res->getBody(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
