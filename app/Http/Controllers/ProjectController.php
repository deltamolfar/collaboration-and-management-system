<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $hasViewAllPermission = $user->hasAbility('project.view_all');
        
        // Base query - Change 'user' to 'owner'
        $query = Project::query()
            ->with('owner')
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function($query) {
                $query->where('status', 'completed');
            }]);
            
        // Apply access restrictions unless user has view_all permission
        if (!$hasViewAllPermission) {
            $query->where('user_id', $user->id);
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('owner_id')) {
            $query->where('user_id', $request->input('owner_id'));
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        
        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        // Handle special sort cases
        if ($sortBy === 'tasks_count') {
            // We need to use orderByRaw for relationships count sorting
            $query->orderByRaw("(SELECT COUNT(*) FROM tasks WHERE tasks.project_id = projects.id) {$sortOrder}");
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        // Get statistics for the dashboard cards
        $stats = [
            'totalProjects' => Project::when(!$hasViewAllPermission, function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            
            'activeProjects' => Project::when(!$hasViewAllPermission, function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('status', ['open', 'in_progress', 'paused'])->count(),
            
            'completedProjects' => Project::when(!$hasViewAllPermission, function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'completed')->count(),
            
            'totalTasks' => Task::when(!$hasViewAllPermission, function($q) use ($user) {
                $q->whereHas('project', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })->count(),
        ];
        
        return Inertia::render('Projects/Index', [
            'projects' => $query->paginate(9),
            'filters' => $request->only(['search', 'status', 'owner_id', 'date_from', 'date_to', 'sort_by', 'sort_order', 'view_mode']),
            'users' => $hasViewAllPermission ? User::select('id', 'name')->get() : [],
            'stats' => $stats
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Create', [
            'statuses' => \App\Models\GlobalSetting::where('key', 'project_statuses')
                ->first()->value,
            'users' => User::all(),
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
            'project' => Project::with('tasks.users')->with('owner')->findOrFail($project->id),
        ]);
    }

    public function edit(Project $project)
    {
        $user = request()->user();
        
        // Check if user has permission to edit this project
        if (!$user->hasAbility('project.update') && $project->user_id !== $user->id) {
            return redirect()->route('projects.index')->with('error', 'You do not have permission to edit this project.');
        }
        
        return Inertia::render('Projects/Edit', [
            'project' => Project::with('owner')->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'completed');
            }])->findOrFail($project->id),
            'users' => $user->hasAbility('project.manage') ? User::select('id', 'name')->get() : [],
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user has permission to edit this project
        if (!$user->hasAbility('project.update') && $project->user_id !== $user->id) {
            return redirect()->route('projects.index')->with('error', 'You do not have permission to edit this project.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'owner_id' => 'required|exists:users,id',
            'statuses' => 'sometimes|array',
        ]);

        // Only admin/managers can change owner
        if (!$user->hasAbility('project.manage')) {
            $request->merge(['owner_id' => $project->user_id]);
        }

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'user_id' => $request->owner_id,
            'statuses' => $request->statuses ?? $project->statuses,
        ]);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user has permission to delete this project
        if (!$user->hasAbility('project.delete') && $project->user_id !== $user->id) {
            return redirect()->route('projects.index')->with('error', 'You do not have permission to delete this project.');
        }
        
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}