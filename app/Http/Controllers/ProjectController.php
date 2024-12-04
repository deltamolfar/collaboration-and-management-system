<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Projects/Index', [
            'projects' => Project::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Create', [
            'statuses' => \App\Models\GlobalSetting::where('key', 'project_statuses')
                ->first()->value,
            'users' => \App\Models\User::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'statuses' => 'nullable|array',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        if(!$request->has('statuses')) {
            $data['statuses'] = \App\Models\GlobalSetting::where('key', 'project_statuses')->first()->value;
        }
        Project::create($data);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Request $request, Project $project)
    {
        if( !$request->user()->hasAbility('project.view_all') ) {
            return redirect()->back()->with('error', 'You do not have permission to view this page.');
        }
    
        return Inertia::render('Projects/Show', [
            'project' => $project::with('tasks.users')->with('owner')->first(),
        ]);
    }

    public function edit(Project $project)
    {
        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'statuses' => 'required|array',
            'status' => 'required|string',
            'owner_id' => 'required|exists:users,id',
        ]);

        $project->update($request->all());

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}