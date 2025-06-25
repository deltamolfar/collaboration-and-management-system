<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $hasViewAllPermission = $user->hasAbility('project.view_all');
        
        // Base query
        $query = Task::query()
            ->with(['project', 'users'])
            ->select('tasks.*');
            
        // Apply access restrictions unless user has view_all permission
        if (!$hasViewAllPermission) {
            $query->where(function($q) use ($user) {
                // Tasks assigned to the user
                $q->whereHas('users', function($query) use ($user) {
                    $query->where('users.id', $user->id);
                });
                
                // Or tasks in projects where the user is a member
                $q->orWhereHas('project', function($query) use ($user) {
                    $query->whereHas('users', function($subQuery) use ($user) {
                        $subQuery->where('users.id', $user->id);
                    });
                });
            });
        }
        
        // Apply filters
        $status = $request->get('status');
        if ($status) {
            $query->where('status', $status);
        }
        
        $projectId = $request->get('project');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $filter = $request->get('filter');
        if ($filter === 'upcoming') {
            $query->where('due_date', '>=', now())
                  ->where('due_date', '<=', now()->addDays(7))
                  ->where('status', '!=', 'completed');
        } elseif ($filter === 'overdue') {
            $query->where('due_date', '<', now())
                  ->where('status', '!=', 'completed');
        } elseif ($filter === 'completed') {
            $query->where('status', 'completed');
        }
        
        // Search functionality
        $search = $request->get('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Sort by due date by default (closest first)
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Get all projects for the filter dropdown
        $projects = $hasViewAllPermission 
            ? Project::select('id', 'name')->get() 
            : Project::whereHas('users', function($q) use ($user) {
                $q->where('users.id', $user->id);
              })->select('id', 'name')->get();
        
        return Inertia::render('Tasks/Index', [
            'tasks' => $query->paginate(10),
            'filters' => [
                'status' => $status,
                'project' => $projectId,
                'filter' => $filter,
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
            'projects' => $projects,
            'can' => [
                'viewAll' => $hasViewAllPermission,
            ]
        ]);
    }

    public function show (Request $request, Project $project, Task $task) {
        if(!$project || !$task) {
            return Inertia::render('Errors/404',
                ['message' => 'Project or Task not found.']
            );
        }

        return Inertia::render('Tasks/Show', [
            'project' => $project,
            'task' => $task::with('users')->find($task->id),
            'comments' => \App\Models\TaskComment::with('user')->where('task_id', $task->id)->get(),
            'users' => \App\Models\User::all(),
            'logs' => \App\Models\TaskLog::with('user')->where('task_id', $task->id)->get(),
        ]);
    }

    public function create (Request $request, Project $project) {
        if(!$project) {
            return Inertia::render('Errors/404',
                ['message' => 'Project not found.']
            );
        }

        return Inertia::render('Tasks/Create', [
            'project' => $project,
            'users' => \App\Models\User::with('role')->get(),
        ]);
    }

    public function store (Request $request, Project $project) 
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'required|string|max:1024',
            'due_date'      => 'required|date',
            'assignees'     => 'required|array|min:1',
            'status'        => 'required|string|max:255',
        ]);

        $data['users'] = $data['assignees'];
        $data['project_id'] = $project->id;
        $data['user_id'] = $request->user()->id;

        if(!$project) {
            return response()->json(['error' => 'Project not found.'], 404);
        }

        $newTask = $project->tasks()->create($data);
        $newTask->users()->attach($data['assignees']);
        $newTask->save();

        // Ensure all assigned users are also members of the project
        $project->users()->syncWithoutDetaching($data['assignees']);

        return redirect()->route('tasks.show', [
            'project' => $project,
            'task' => $newTask,
        ])->with('success', 'Task created successfully.');
    }

    public function edit (Request $request) {
        $project = $request->input('project');
        $task = $request->input('task');
        if(!$project || !$task) {
            return Inertia::render('Errors/404',
                ['message' => 'Project or Task not found.']
            );
        }

        return Inertia::render('Tasks/Edit', [
            'project' => $project,
            'task' => $task,
            'users' => $project->users()->get(),
        ]);
    }

    public function update (Request $request, Project $project, Task $task) 
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'required|string|max:1024',
            'due_date'      => 'required|date',
            'assignees'     => 'required|string|min:1',
            'status'        => 'required|string|max:255',
        ]);

        if(!$project || !$task) {
            return Inertia::render('Errors/404',
                ['message' => 'Project or Task not found.']
            );
        }

        $task->update($request->all());

        // Ensure all assigned users are also members of the project
        $assignees = is_array($request->assignees) ? $request->assignees : explode(',', $request->assignees);
        $project->users()->syncWithoutDetaching($assignees);

        return response("success", status: 200);
    }

    public function destroy (Request $request, Project $project, Task $task) {
        if(!$project || !$task) {
            return response()->json(['error' => 'Project or Task not found.'], 404);
        }

        $task->delete();

        return response("success", status: 200);
    }
}
