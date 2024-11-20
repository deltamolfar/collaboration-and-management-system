<?php

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard');
    }

    public function globalSettingsIndex()
    {
        return Inertia::render('Admin/GlobalSettings/Index');
    }

    public function globalSettingsUpdate(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        GlobalSetting::updateOrCreate(
            ['key' => $request->input('key')],
            ['value' => $request->input('value')]
        );

        return redirect()->route('admin.global_settings.index')->with('success', 'Global setting updated successfully.');
    }
}
