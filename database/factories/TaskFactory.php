<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => 'open',
            'user_id' => \App\Models\User::factory(),
            'project_id' => \App\Models\Project::factory(),
            'time_estimate' => $this->faker->numberBetween(1, 100),
        ];
    }
}