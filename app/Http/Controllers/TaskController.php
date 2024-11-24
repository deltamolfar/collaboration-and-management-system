<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index (Request $request) {
        $project = $request->input('project');
        if(!$project) {
            return Inertia::render('Errors/404');
        }

        return Inertia::render('Tasks/Index', [
            'project' => $project,
            'tasks' => $project->tasks()->get(),
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

    public function store (Request $request, Project $project) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'due_date' => 'required|date',
            'assignees' => 'required|array|min:1',
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

    public function update (Request $request) {
        $request->validate([
            'project_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'user_id' => 'required|integer',
            'due_date' => 'required|date',
        ]);

        $project = Project::find($request->input('project_id'));
        $task = $request->input('task');
        if(!$project || !$task) {
            return Inertia::render('Errors/404',
                ['message' => 'Project or Task not found.']
            );
        }

        $task->update($request->all());

        return redirect()->route('tasks.index', ['project' => $project])->with('success', 'Task updated successfully.');
    }

    public function destroy (Request $request) {
        $project = $request->input('project');
        $task = $request->input('task');
        if(!$project || !$task) {
            return Inertia::render('Errors/404',
                ['message' => 'Project or Task not found.']
            );
        }

        $task->delete();

        return redirect()->route('tasks.index', ['project' => $project])->with('success', 'Task deleted successfully.');
    }

}
