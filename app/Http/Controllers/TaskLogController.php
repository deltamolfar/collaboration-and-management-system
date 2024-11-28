<?php

namespace App\Http\Controllers;

use App\Models\TaskLog;
use Illuminate\Http\Request;

class TaskLogController extends Controller
{
    public function index(){
        return TaskLog::all();
    }

    public function store(Request $request){
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'time_spent' => 'required|integer',
            'time_start' => 'nullable|date',
            'time_end' => 'nullable|date',
        ]);

        return TaskLog::create($request->all());
    }

    public function update(Request $request, TaskLog $log){
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'time_spent' => 'required|integer',
            'time_start' => 'nullable|date',
            'time_end' => 'nullable|date',
        ]);

        $log->update($request->all());
        return $log;
    }

    public function destroy(TaskLog $log){
        $log->delete();
        return response()->json(['message' => 'Task log deleted successfully']);
    }
}
