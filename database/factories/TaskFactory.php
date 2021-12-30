<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Project;
use App\Models\User;

class TaskFactory extends Factory
{
    public function definition()
    {
        $project = Project::inRandomOrder()->first();

        // makes this task either a top task or a subtask
        $parentId = (rand(1,10)>3 && $project->tasks->count()>0)
                        ? $project->tasks->shuffle()->first()->id
                        : null;

        $nHours = rand(1,50);

        $creator = $project->members->shuffle()->first();

        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'project_id' =>  $project->id,
            'created_by' => $creator->id,
            'reporter_id' => $creator->id,
            'assignee_id' => $project->members->shuffle()->first()->id,
            'parent_id' => $parentId,
            'priority' => rand(1,5),
            'estimation_realistic' => $nHours*2,
            'estimation_optimistic' => $nHours,
            'estimation_pessimistic' => $nHours*4,
            'estimation_calculated' => $nHours*3
        ];
    }


    /**
    * Set the task to belonging to a given project
    *
    * @param Project $project
    * @return \Illuminate\Database\Eloquent\Factories\Factory
    */
    public function forProject(Project $project)
    {
        return $this->state(function (array $attributes) use ($project) {
            
            $creator = $project->members->shuffle()->first();

            return [
                'project_id' => $project->id,
                'created_by' => $creator->id,
                'reporter_id' => $creator->id,
                'assignee_id' => $project->members->shuffle()->first()->id,
            ];
        })/*->afterMaking(function (Task $task) use ($project) {
            // $task->doSomething();
        })->afterCreating(function (Task $task) use ($project) {
            // $task->doSomething();
        })*/;
    }
}
