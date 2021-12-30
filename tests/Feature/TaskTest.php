<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

use Tests\TestCase;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;

class TaskTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_eloquent_relationships()
    {
        $this->seed();

        $project = Project::factory()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $project->members()->attach($userA, ['ROLE' => ProjectMember::ROLE_WRITE]);
        $project->members()->attach($userB, ['ROLE' => ProjectMember::ROLE_WRITE]);
        $task = Task::factory()
                    ->forProject($project)
                    ->create([
                        'created_by' => $userA->id,
                        'reporter_id' => $userA->id,
                        'assignee_id' => $userB->id
                    ]);

        $nSubtasks = 5;
        Task::factory($nSubtasks)->forProject($project)->create(['parent_id' => $task->id]);

        $this->assertInstanceOf(Project::class, $task->project);
        $this->assertEquals($project->id, $task->project->id);

        $this->assertInstanceOf(User::class, $task->reporter);
        $this->assertEquals($userA->id, $task->reporter->id);

        $this->assertInstanceOf(User::class, $task->assignee);
        $this->assertEquals($userB->id, $task->assignee->id);

        $this->assertEquals($nSubtasks, $task->children->count());
    }

    /**
     * Tests that user can index all tasks for a given project.
     * User must be a project member.
     */
    public function test_index_tasks()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();
        $nTasks = $project->tasks->count();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $project->members()->attach($userA);

        $endpoint = "/api/projects/{$project->id}/tasks";

        Sanctum::actingAs($userB, ['*']);
        $response = $this->json('GET', $endpoint);
        $response->assertStatus(403);

        Sanctum::actingAs($userA, ['*']);
        $response = $this->json('GET', $endpoint);
        $response->assertStatus(200);

        $nTasksRetrieved = sizeof(json_decode($response->content()));

        $this->assertEquals($nTasks, $nTasksRetrieved);
    }

    /**
     * Tests that user can create a task for a project of
     * which he is a member and has `WRITE` permissions.
     */
    public function test_create_task()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();

        $nTasks_preRequest = $project->tasks->count();

        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        $project->members()->attach($userA, ['role' => ProjectMember::ROLE_WRITE]);
        $project->members()->attach($userB, ['role' => ProjectMember::ROLE_READ]);

        $data = [
            'title' => "Design Landing Page",
            'description' => $this->faker->paragraph(),
            'project_id' => $project->id,
            'estimation_realistic' => 6,
            'estimation_optimistic' => 3,
            'estimation_pessimistic' => 25,
            'estimation_calculated' => 11
        ];

        $url = "/api/projects/{$project->id}/tasks";

        // user who is not a project member
        Sanctum::actingAs($userC, ['*']);
        $response = $this->json('POST', $url, $data);
        $response->assertStatus(403);
        $this->assertEquals($nTasks_preRequest, $project->refresh()->tasks->count());

        // user who is a project member but only has READ permissions
        Sanctum::actingAs($userB, ['*']);
        $response = $this->json('POST', $url, $data);
        $response->assertStatus(403);
        $this->assertEquals($nTasks_preRequest, $project->refresh()->tasks->count());

        // user who is a project member and has WRITE permissions
        Sanctum::actingAs($userA, ['*']);
        $response = $this->json('POST', $url, $data);
        $response->assertStatus(201);
        $this->assertEquals($nTasks_preRequest + 1, $project->refresh()->tasks->count());

        // retrieve the newly created Task and test the auto-generated values
        $task = Task::find((int) json_decode($response->content())->id);
        $this->assertEquals($userA->id, $task->created_by);
        $this->assertEquals($userA->id, $task->reporter_id);
    }

    public function test_create_subtask()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => ProjectMember::ROLE_WRITE]);
        $task = $project->tasks->shuffle()->first();

        $data = [
            'title' => 'Lorem Ipsum Dolorem',
            'parent_id' => $task->id
        ];

        $nTasks_preRequest = Task::count();
        $nSubtasks_preRequest = $task->children->count();

        Sanctum::actingAs($user, ['*']);
        $response = $this->json('POST', "api/projects/{$project->id}/tasks", $data);
        $response->assertStatus(201);
        $this->assertEquals($nTasks_preRequest+1, Task::count());
        $this->assertEquals($nSubtasks_preRequest+1, $task->refresh()->children->count());
    }

    public function test_retrieve_task()
    {
        $this->seed();

        $task = Task::inRandomOrder()->first();
        $userA = $task->project->members->shuffle()->first();
        $userB = User::factory()->create();

        Sanctum::actingAs($userA, ['*']);
        $response = $this->json('GET', '/api/tasks/'.$task->id);
        $response->assertStatus(200);

        Sanctum::actingAs($userB, ['*']);
        $response = $this->json('GET', '/api/tasks/'.$task->id);
        $response->assertStatus(403);
    }

    /**
     * Tests that an authorized user can edit a Task and that only
     * certain fields can be edited.
     */
    public function test_update_task()
    {
        $this->seed();

        $task = Task::inRandomOrder()->first();
        $project = $task->project;
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => ProjectMember::ROLE_WRITE]);

        $data = [
            // editable fields
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'assignee_id' => $project->members->shuffle()->first()->id,
            'priority' => rand(1,5),
            'estimation_realistic' => 1,
            'estimation_optimistic' => 1,
            'estimation_pessimistic' => 1,
            'estimation_calculated' => 1,

            // uneditable fields
            'project_id' => Project::where('id', '!=', $task->project_id)->inRandomOrder()->first()->id,
            'created_by' => User::inRandomOrder('id', '!=', $task->created_by)->first()->id
        ];

        Sanctum::actingAs($user, ['*']);
        $response = $this->json('PATCH', '/api/tasks/'.$task->id, $data);
        $response->assertStatus(200);

        $task->refresh();

        $this->assertEquals($data['title'], $task->title);
        $this->assertEquals($data['description'], $task->description);
        $this->assertEquals($data['assignee_id'], $task->assignee_id);
        $this->assertEquals($data['priority'], $task->priority);
        $this->assertEquals($data['estimation_realistic'], $task->estimation_realistic);
        $this->assertEquals($data['estimation_optimistic'], $task->estimation_optimistic);
        $this->assertEquals($data['estimation_pessimistic'], $task->estimation_pessimistic);
        $this->assertEquals($data['estimation_calculated'], $task->estimation_calculated);

        $this->assertNotEquals($data['project_id'], $task->project_id);
        $this->assertNotEquals($data['created_by'], $task->created_by);

    }

    /**
     * Tests that only authorized project members can delete a given task. 
     */
    public function test_delete_task()
    {
        $this->seed();

        $task = Task::inRandomOrder()->first();
        $project = $task->project;
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $project->members()->attach($userA, ['role' => ProjectMember::ROLE_WRITE]);
        $nProjectTasks_preRequest = $project->tasks->count();

        $url = 'api/tasks/'.$task->id;

        Sanctum::actingAs($userB, ['*']);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(403);
        $this->assertEquals($nProjectTasks_preRequest, $project->refresh()->tasks->count());


        Sanctum::actingAs($userA, ['*']);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(200);
        $this->assertEquals($nProjectTasks_preRequest-1, $project->refresh()->tasks->count());

    }

    public function test_subtasks_are_automatically_deleted_when_parent_task_is_deleted()
    {
        $this->seed();

        $nSubtasks = 5;

        $project = Project::factory()->create();
        $members = User::inRandomOrder()->take(3)->get();
        $project->members()->attach($members, ['role' => ProjectMember::ROLE_WRITE]);
        $task = Task::factory()->forProject($project)->create();
        $subtasks = Task::factory($nSubtasks)->forProject($project)->create(['parent_id' => $task->id]);


        $user = $project->members->shuffle()->first();

        $nTasks_preRequest = Task::count();

        Sanctum::actingAs($user, ['*']);
        $response = $this->json('DELETE', '/api/tasks/'.$task->id);
        $response->assertStatus(200);

        $this->assertEquals($nTasks_preRequest - ($nSubtasks+1), Task::count());
    }

    // *PENDING*
    public function SKIPtest_cannot_reassign_task_to_non_member(){

    }
}