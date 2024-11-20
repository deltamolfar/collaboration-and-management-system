<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);
        $task = $request->input('task');
        $project = $request->input('project');

        if( $task ){
            return [
                'comments' => $task->comments()->paginate($perPage, ['*'], 'page', $page),
            ];
        } elseif( $project ) {
            return [
                'comments' => $project->comments()->paginate($perPage, ['*'], 'page', $page),
            ];
        } else {
            return Inertia::render('Errors/404');
        }
    }

    public function timesheet()
    {
        return Inertia::render('Timesheet');
    }
}
