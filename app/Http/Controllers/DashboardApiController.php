<?php
// filepath: /home/deltamolfar/Documents/Dev/deltamolfar/pnu-diplom/app/Http/Controllers/DashboardApiController.php

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
        $canViewAll = $user->role->abilities && in_array('project.view_all', $user->role->abilities);
        
        // Base queries
        $projectsQuery = Project::query();
        $tasksQuery = Task::query();
        
        // Apply access restrictions if needed
        if (!$canViewAll) {
            // Only show projects owned by user or where user is assigned to tasks
            $projectsQuery->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('tasks', function($q) use ($user) {
                          $q->whereHas('users', function($q2) use ($user) {
                              $q2->where('users.id', $user->id);
                          });
                      });
            });
            
            // Only show tasks assigned to user
            $tasksQuery->whereHas('users', function($query) use ($user) {
                $query->where('users.id', $user->id);
            });
        }
        
        // Count upcoming deadlines (due in next 7 days)
        $upcomingDeadlines = $tasksQuery->clone()
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->count();
            
        // Count completed tasks
        $completedTasks = $tasksQuery->clone()
            ->where('status', 'completed')
            ->count();
        
        // Total tasks
        $totalTasks = $tasksQuery->count();
        
        return response()->json([
            'totalProjects' => $projectsQuery->count(),
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'upcomingDeadlines' => $upcomingDeadlines
        ]);
    }
    
    public function activity(Request $request)
    {
        $user = $request->user();
        $canViewAll = $user->role->abilities && in_array('project.view_all', $user->role->abilities);
        
        // Get recently created or updated tasks
        $tasks = Task::with(['project.owner', 'users', 'comments.user'])
            ->when(!$canViewAll, function($query) use ($user) {
                // Only show tasks assigned to user or from user's projects
                $query->where(function($q) use ($user) {
                    $q->whereHas('users', function($q2) use ($user) {
                        $q2->where('users.id', $user->id);
                    })
                    ->orWhereHas('project', function($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
                });
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($task): array => [
                    'id' => "task-$task->id",
                    'type' => 'task',
                    'user_name' => $task->user->name ?? 'Unknown',
                    'description' => "created task '{$task->name}' in project '{$task->project->name}'",
                    'created_at' => $task->created_at
                ]
            );
        
        // Get recent comments
        $comments = TaskComment::with(['task.project', 'user'])
            ->when(!$canViewAll, function($query) use ($user) {
                // Only show comments on tasks assigned to user or from user's projects
                $query->whereHas('task', function($q) use ($user) {
                    $q->whereHas('users', function($q2) use ($user) {
                        $q2->where('users.id', $user->id);
                    })
                    ->orWhereHas('project', function($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
                });
            })
            ->latest()
            ->limit(5)
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
        
        // Combine and sort by date
        $activity = $tasks->concat($comments)
            ->sortByDesc('created_at')
            ->values()
            ->take(10);
        
        return response()->json($activity);
    }
    
    public function upcomingTasks(Request $request)
    {
        $user = $request->user();
        $canViewAll = $user->role->abilities && in_array('project.view_all', $user->role->abilities);
        
        $tasks = Task::with(['project', 'comments'])
            ->when(!$canViewAll, function($query) use ($user) {
                // Only show tasks assigned to user or from user's projects
                $query->where(function($q) use ($user) {
                    $q->whereHas('users', function($q2) use ($user) {
                        $q2->where('users.id', $user->id);
                    })
                    ->orWhereHas('project', function($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
                });
            })
            // Not completed and has due date
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            // Due date is in the future or today
            ->whereDate('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get()
            ->map(fn($task): array =>  [
                    'id' => $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'status' => $task->status,
                    'due_date' => $task->due_date,
                    'project_id' => $task->project_id,
                    'comments_count' => $task->comments->count()
                ]
            );
        
        return response()->json($tasks);
    }
}