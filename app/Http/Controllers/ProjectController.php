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
            'paginatedProjects' => Project::query()->paginate($request->input('limit', 50)),
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'api_name' => 'required|unique:projects,api_name',
            'name' => 'required|string|max:255',
            'statuses' => 'required|array',
            'status' => 'required|string',
            'owner_id' => 'required|exists:users,id',
        ]);

        Project::create($request->all());

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        #dd($project);
        return Inertia::render('Projects/Show', [
            'project' => $project,
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
            'api_name' => 'required|unique:projects,api_name,' . $project->id,
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