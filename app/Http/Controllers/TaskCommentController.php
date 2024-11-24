<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Project $project, Task $task)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        $task->comments()->create([
            'comment' => $data['comment'],
            'user_id' => $request->user()->id,
        ]);

        return back();
    }
}
