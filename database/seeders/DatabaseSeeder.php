<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskComment;
use App\Models\Project;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create users
        $users = User::factory(10)->create();

        // Create projects
        $projects = Project::factory(5)->create([
            'user_id' => 1, // Assign to existing user with id 1
        ]);

        // Create tasks
        $tasks = Task::factory(20)->create([
            'user_id' => 1, // Assign to existing user with id 1
            'project_id' => $projects->random()->id,
        ]);

        // Create task logs
        TaskLog::factory(50)->create([
            'task_id' => $tasks->random()->id,
            'user_id' => 1, // Assign to existing user with id 1
        ]);

        // Create task comments
        TaskComment::factory(50)->create([
            'task_id' => $tasks->random()->id,
            'user_id' => 1, // Assign to existing user with id 1
        ]);
    }
}