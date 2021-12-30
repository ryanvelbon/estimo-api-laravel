<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

use App\Models\Project;
use App\Models\Task;

class TasksTableSeeder extends Seeder
{
    public function run()
    {
        $min = Config::get('seeding.n_tasks_per_project_min');
        $max = Config::get('seeding.n_tasks_per_project_max');

        foreach(Project::all() as $project) {
            Task::factory(rand($min,$max))->forProject($project)->create();
        }
    }
}
