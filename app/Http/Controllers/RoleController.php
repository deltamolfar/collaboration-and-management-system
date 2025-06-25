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

        return response()->json([
            'message' => 'Role created successfully.',
            'role' => $request->all(),
        ]);
    }

    public function update (Request $request, Role $role) {
        $request->validate([
            'api_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'abilities' => 'required|array|in:' . implode(',', Role::ability_list),
        ]);

        $role->update($request->all());

        return response()->json([
            'message' => 'Role updated successfully.',
            'role' => $role,
        ]);
    }
}
