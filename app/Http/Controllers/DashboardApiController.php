<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();
        $hasViewAllPermission = $user->hasAbility('project.view_all');
        
        $projectsQuery = Project::query();
        $tasksQuery = Task::query();
        
        // Apply access restrictions if needed
        if (!$hasViewAllPermission) {
            $projectsQuery->where('user_id', $user->id);
            $tasksQuery->whereHas('project', function($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        
        // Get upcoming deadlines (tasks due in the next 7 days)
        $upcomingDeadlines = $tasksQuery->clone()
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->count();
        
        return response()->json([
            'totalProjects' => $projectsQuery->count(),
            'totalTasks' => $tasksQuery->count(),
            'completedTasks' => $tasksQuery->clone()->where('status', 'completed')->count(),
            'upcomingDeadlines' => $upcomingDeadlines
        ]);
    }
    
    public function activity(Request $request)
    {
        $user = $request->user();
        $hasViewAllPermission = $user->hasAbility('project.view_all');
        
        // Get recent activity (tasks created/updated, comments, etc.)
        $activities = collect();
        
        // Get recent task updates
        $tasks = Task::when(!$hasViewAllPermission, function($query) use ($user) {
                $query->whereHas('project', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function($task) {
                return [
                    'id' => 'task-' . $task->id,
                    'type' => 'task',
                    'user_name' => $task->project->owner->name ?? 'Unknown',
                    'description' => "created task '{$task->name}' in project '{$task->project->name}'",
                    'created_at' => $task->created_at
                ];
            });
        
        $activities = $activities->concat($tasks);
        
        // Get recent comments
        $comments = TaskComment::whereHas('task', function($query) use ($user, $hasViewAllPermission) {
                $query->whereHas('project', function($query) use ($user, $hasViewAllPermission) {
                    if (!$hasViewAllPermission) {
                        $query->where('user_id', $user->id);
                    }
                });
            })
            ->with(['task.project', 'user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($comment) {
                return [
                    'id' => 'comment-' . $comment->id,
                    'type' => 'comment',
                    'user_name' => $comment->user->name ?? 'Unknown',
                    'description' => "commented on task '{$comment->task->name}'",
                    'created_at' => $comment->created_at
                ];
            });
        
        $activities = $activities->concat($comments);
        
        // Sort by created_at and take the most recent 10
        $activities = $activities->sortByDesc('created_at')->take(10)->values();
        
        return response()->json($activities);
    }
    
    public function upcomingTasks(Request $request)
    {
        $user = $request->user();
        $hasViewAllPermission = $user->hasAbility('project.view_all');
        
        $tasks = Task::with(['project', 'comments'])
            ->when(!$hasViewAllPermission, function($query) use ($user) {
                $query->whereHas('project', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->where(function($query) {
                $query->where('status', '!=', 'completed')
                      ->whereNotNull('due_date')
                      ->whereDate('due_date', '>=', now());
            })
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(function($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'due_date' => $task->due_date,
                    'project_id' => $task->project_id,
                    'comments_count' => $task->comments->count()
                ];
            });
        
        return response()->json($tasks);
    }
    
    public function announcements()
    {
        // In a real app, you'd fetch from a database
        // Here we'll return mock data
        $announcements = [
            [
                'id' => 1,
                'title' => 'Welcome to Project Management',
                'content' => 'This is your new project management system. Explore the features!',
                'created_at' => now()->subDays(2)
            ]
        ];
        
        return response()->json($announcements);
    }
}
