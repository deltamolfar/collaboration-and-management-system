<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'api_name' => $this->faker->unique()->slug,
            'name' => $this->faker->company,
            'status' => 'open',
            'user_id' => User::factory(),
        ];
    }
}