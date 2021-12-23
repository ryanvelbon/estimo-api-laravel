<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Project;
use App\Models\User;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        $user = User::inRandomOrder()->first();

        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => rand(1,5), // *REVISE*
            'created_by' => $user->id,
            'managed_by' => $user->id
        ];
    }
}
