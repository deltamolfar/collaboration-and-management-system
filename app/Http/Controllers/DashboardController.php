<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $hasViewAllPermission = $user->hasAbility('project.view_all');
        
        // Preload stats for the dashboard
        $stats = [
            'totalProjects' => Project::when(!$hasViewAllPermission, function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            
            'totalTasks' => Task::when(!$hasViewAllPermission, function($q) use ($user) {
                $q->whereHas('project', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })->count(),
            
            'completedTasks' => Task::when(!$hasViewAllPermission, function($q) use ($user) {
                $q->whereHas('project', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })->where('status', 'completed')->count(),
            
            'upcomingDeadlines' => Task::when(!$hasViewAllPermission, function($q) use ($user) {
                $q->whereHas('project', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            // You can include other data here as needed
        ]);
    }

    public function timesheet(Request $request)
    {
        $user = $request->user();
        $hasViewAllPermission = $user->hasAbility('task.log.view_all');

        // Filters
        $filters = [
            'search' => $request->input('search'),
            'user_id' => $request->input('user_id'),
            'project_id' => $request->input('project_id'),
            'task_id' => $request->input('task_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'group_by' => $request->input('group_by', 'date'),
            'sort_by' => $request->input('sort_by', 'date'),
            'sort_order' => $request->input('sort_order', 'desc'),
            'view_mode' => $request->input('view_mode', 'detailed'),
            'billable_only' => $request->input('billable_only'),
            'current_user_only' => $request->input('current_user_only'),
        ];

        // Query logs
        $logsQuery = TaskLog::with(['task.project', 'user']);

        if (!$hasViewAllPermission || $filters['current_user_only'] === 'true') {
            $logsQuery->where('user_id', $user->id);
        } elseif ($filters['user_id']) {
            $logsQuery->where('user_id', $filters['user_id']);
        }

        if ($filters['project_id']) {
            $logsQuery->whereHas('task', function($q) use ($filters) {
                $q->where('project_id', $filters['project_id']);
            });
        }

        if ($filters['task_id']) {
            $logsQuery->where('task_id', $filters['task_id']);
        }

        if ($filters['date_from']) {
            $logsQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $logsQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        if ($filters['search']) {
            $logsQuery->where('description', 'like', '%' . $filters['search'] . '%');
        }

        if ($filters['billable_only'] === 'true') {
            $logsQuery->whereHas('task', function($q) {
                $q->where('billable_minutes', '>', 0);
            });
        }

        // Sorting
        if ($filters['sort_by'] === 'time') {
            $logsQuery->orderBy('time_spent', $filters['sort_order']);
        } else {
            $logsQuery->orderBy('created_at', $filters['sort_order']);
        }

        $logs = $logsQuery->paginate(20);

        // Projects and users for filters
        $projects = $hasViewAllPermission
            ? Project::all(['id', 'name'])
            : Project::where('user_id', $user->id)->get(['id', 'name']);

        $users = $hasViewAllPermission
            ? User::all(['id', 'name'])
            : User::where('id', $user->id)->get(['id', 'name']);

        // Summary
        $summaryQuery = clone $logsQuery;
        $summary = [
            'total_time' => (clone $summaryQuery)->sum('time_spent'),
            'billable_time' => (clone $summaryQuery)->whereHas('task', function($q) {
                $q->where('billable_minutes', '>', 0);
            })->sum('time_spent'),
            'non_billable_time' => (clone $summaryQuery)->whereHas('task', function($q) {
                $q->where(function($q2) {
                    $q2->whereNull('billable_minutes')->orWhere('billable_minutes', 0);
                });
            })->sum('time_spent'),
        ];

        return Inertia::render('Timesheet', [
            'logs' => $logs,
            'filters' => $filters,
            'projects' => $projects,
            'users' => $users,
            'summary' => $summary,
        ]);
    }
}
