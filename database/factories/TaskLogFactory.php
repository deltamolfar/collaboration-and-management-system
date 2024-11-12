<?php

namespace Database\Factories;

use App\Models\TaskLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskLogFactory extends Factory
{
    protected $model = TaskLog::class;

    public function definition()
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'comment' => $this->faker->sentence,
            'time_start' => $this->faker->dateTime,
            'time_end' => $this->faker->dateTime,
            'time_spent' => $this->faker->numberBetween(1, 8),
        ];
    }
}