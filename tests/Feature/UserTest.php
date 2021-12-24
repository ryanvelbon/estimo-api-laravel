<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrieve_current_user_using_bearer_token()
    {
        $this->seed();

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);
        $response = $this->json('GET', 'api/users/me');
        $response->assertStatus(200);
        $this->assertEquals($user->email, json_decode($response->content())->email);
    }

    public function test_retrieve_user_using_id()
    {
        $this->seed();

        $user = User::inRandomOrder()->first();

        $response = $this->json('GET', 'api/users/'.$user->id);
        $response->assertStatus(200);
        $this->assertEquals($user->email, json_decode($response->content())->email);
    }
}
