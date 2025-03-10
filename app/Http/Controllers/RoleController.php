<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index (Request $request) {
        return Inertia::render('Admin/Roles');
    }

    public function store (Request $request) {
        $request->validate([
            'api_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'abilities' => 'required|array|in:' . implode(',', Role::ability_list),
        ]);

        Role::create($request->all());

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }
}
