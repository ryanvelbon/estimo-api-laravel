<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;

/*
 * This script features a fair bit of reused code from Koolabo's `ProjectTest.php`
 *
 */

class ProjectTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /*
     * POST & PATCH requests to `/api/projects` should not include `created_by` and
     * `managed_by` fields since these fields are set by the Controller.
     *
     */
    private function generate_dummy_form_data()
    {
        $data = Project::factory()->make()->toArray();
        unset($data['created_by']);
        unset($data['managed_by']);

        return $data;
    }

    public function test_eloquent_relationships()
    {
        $this->seed();

        $nMembers = 4; // team size

        $user = User::inRandomOrder()->first();
        $project = Project::factory()->for($user, 'manager')->create();
        $project->members()->attach(User::inRandomOrder()->take($nMembers)->get());

        $project->refresh();

        $this->assertEquals($user->id, $project->manager->id);
        $this->assertEquals($nMembers, $project->members->count());
    }

    public function test_user_can_only_index_projects_they_are_a_member_of()
    {
        $this->seed();

        $user = User::inRandomOrder()->first();

        $nProjectsCurrentUserIsMember = $user->projects->count();

        Sanctum::actingAs($user, ['*']);
        $response = $this->json('GET', 'api/projects');
        $response->assertStatus(200);

        $nProjectsRetrieved = sizeof(json_decode($response->content()));

        $this->assertEquals($nProjectsCurrentUserIsMember, $nProjectsRetrieved);
    }

    /**
     * Tests that if current user is a member of a project, he can retrieve that Project resource.
     *
     * @return void
     */
    public function test_member_can_view_project()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();

        $user = $project->members()->inRandomOrder()->first();

        Sanctum::actingAs($user, ['*']);
        $response = $this->json('GET', '/api/projects/'.$project->id);
        $response->assertStatus(200);
    }

    public function test_non_member_cannot_view_project()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);
        $response = $this->json('GET', '/api/projects/'.$project->id);
        $response->assertStatus(403);
    }
    
    public function test_unauthenticated_user_cannot_create_project()
    {
        $this->seed();

        $data = $this->generate_dummy_form_data();

        $response = $this->json('POST', '/api/projects', $data);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_project()
    {
        $this->seed();

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $data = $this->generate_dummy_form_data();

        $response = $this->json('POST', '/api/projects', $data);
        
        $response->assertStatus(201);
    }

    public function test_unauthorized_user_cannot_delete_project()
    {
        $this->seed();
        
        $project = Project::inRandomOrder()->first();
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $nProjects_preRequest = Project::count();

        $response = $this->json('DELETE', '/api/projects/'.$project->id);
        $response->assertStatus(403);
        $this->assertEquals($nProjects_preRequest, Project::count());
    }

    public function test_project_manager_can_delete_project()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();

        Sanctum::actingAs($project->manager, ['*']);

        $nProjects_preRequest = Project::count();

        $response = $this->json('DELETE', '/api/projects/'.$project->id);
        $response->assertStatus(200);
        $this->assertEquals($nProjects_preRequest-1, Project::count());
    }

    public function test_project_manager_can_edit_project()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();

        Sanctum::actingAs($project->manager, ['*']);

        $newTitle = "This is a New Title Lorem Ipsum";
        $data = ['title' => $newTitle];

        $response = $this->json('PATCH', '/api/projects/'.$project->id, $data);
        $response->assertStatus(200); // should it be 204 instead?
        $this->assertEquals($newTitle, Project::find($project->id)->title);
    }

    /*
     *   Only project manager should be able to edit project
     */
    public function test_unauthorized_user_cannot_edit_project()
    {
        $this->seed();

        $user = User::factory()->create();
        $project = Project::inRandomOrder()->first();

        Sanctum::actingAs($user, ['*']);

        $newTitle = "This is a New Title Lorem Ipsum";
        $data = ['title' => $newTitle];

        $response = $this->json('PATCH', '/api/projects/'.$project->id, $data);
        $response->assertStatus(403);
        $this->assertNotEquals($newTitle, Project::find($project->id)->title);
    }

    public function test_project_creator_is_appointed_as_project_manager_by_default()
    {
        $this->seed();

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $data = $this->generate_dummy_form_data();

        $response = $this->json('POST', '/api/projects', $data);

        $response->assertStatus(201);

        $managerId = (int) json_decode($response->content())->managed_by;

        $this->assertEquals($managerId, $user->id);
    }

    public function test_project_creator_can_appoint_someone_else_as_project_manager()
    {
        $this->seed();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Sanctum::actingAs($userA, ['*']);

        $data = $this->generate_dummy_form_data();

        // user A creates a new project
        $response = $this->json('POST', '/api/projects', $data);
        $response->assertStatus(201);
        $projectId = (int) json_decode($response->content())->id;

        // user A updates project so that user B is now the manager
        $response = $this->json('PATCH', '/api/projects/'.$projectId, ['managed_by' => $userB->id]);
        $response->assertStatus(200); // should it be 204 instead?

        $response = $this->json('GET', '/api/projects/'.$projectId);
        $response->assertStatus(200);
        $managerId = (int) json_decode($response->content())->managed_by;

        $this->assertEquals($managerId, $userB->id);
    }

    // *PENDING*
    public function SKIPtest_project_manager_can_invite_user_to_join_project()
    {
        $this->assertTrue(False);
    }

    // *PENDING*
    public function SKIPtest_project_manager_can_cancel_invitation()
    {
        $this->assertTrue(False);
    }

    // *PENDING*
    public function SKIPtest_user_can_leave_project_unless_is_project_manager()
    {
        $this->assertTrue(False);
    }
}
