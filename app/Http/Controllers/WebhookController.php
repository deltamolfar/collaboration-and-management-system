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
        if (request()->expectsJson()) {
            return Webhook::latest()->get();
        }
        
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
            'header_key' => 'nullable|string',
            'header_value' => 'nullable|string',
        ]);

        Webhook::create($data);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook created successfully.');
    }
    
    public function update(Request $request, Webhook $webhook)
    {
        $data = $request->validate([
            'action' => 'required|string|in:task.create,task.update,task.delete,task.comment,task.log,project.create,project.update,project.delete,role.create,role.update,role.delete,user.create,user.update,user.delete',
            'url' => 'required|url',
            'header_key' => 'nullable|string',
            'header_value' => 'nullable|string',
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
}
