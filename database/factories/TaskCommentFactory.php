<?php

namespace Database\Factories;

use App\Models\TaskComment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskCommentFactory extends Factory
{
    protected $model = TaskComment::class;

    public function definition()
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'comment' => $this->faker->sentence,
            'attachments' => [],
        ];
    }
}