<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return [
            'users' => \App\Models\User::all(),
            'roles' => \App\Models\Role::all(),
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required',
        ]);

        $user = new \App\Models\User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->role_id = $request->input('role');
        $user->save();

        return $user;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id' => 'required|integer',
            'password' => 'nullable|string',
            'role_id' => 'nullable|string',
        ]);

        $user = \App\Models\User::findOrFail($id);
        if(!empty($request->input('password'))) {
            $user->password = bcrypt($request->input('password'));
        }
        if(!empty($request->input('role_id'))) {
            $user->role_id = $request->input('role_id');
        }
        
        $user->save();
        return $user;
    }

    public function destroy($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();

        return response(null, 204);
    }

    public function role(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = \App\Models\User::findOrFail($request->input('user_id'));
        return $user->role();
    }
}
