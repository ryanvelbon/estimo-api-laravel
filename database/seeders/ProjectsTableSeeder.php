<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectMember;

class ProjectsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('project_members')->delete();
        DB::table('projects')->delete();

        $projects = Project::factory()->count(50)->create();

        foreach($projects as $project) {

            // add project owner as member
            $project->members()->attach($project->owner, ['role' => ProjectMember::ROLE_OWNER]);


            // add additional random members
            $members = User::where('id', '!=', $project->created_by)
                            ->inRandomOrder()
                            ->limit(rand(1,4))
                            ->get();
            $project->members()->attach($members, ['role' => rand(3,5)]);
        }
    }
}
