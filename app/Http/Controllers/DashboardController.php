<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
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

    public function timesheet()
    {
        return Inertia::render('Timesheet');
    }
}
