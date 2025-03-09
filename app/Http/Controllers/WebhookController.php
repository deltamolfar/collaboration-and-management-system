<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebhookController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Webhooks');
    }

    public function create()
    {
        return Inertia::render('Admin/WebhookCreate');
    }

    public function store(Request $request, Webhook $webhook)
    {
        $data = $request->validate([
            'action' => 'required|string|in:task.create,task.update,task.delete,task.comment,task.log,project.create,project.update,project.delete,role.create,role.update,role.delete,user.create,user.update,user.delete',
            'url' => 'required|url',
            'header_key' => 'nullable|string',
            'header_value' => 'nullable|string',
        ]);

        $webhook->create($data);

        return redirect()->route('webhooks.index')->with('success', 'Webhook created successfully.');
    }
}
