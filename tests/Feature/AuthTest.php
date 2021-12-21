<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    // use WithFaker;

    /**
     * Tests that guest can sign up.
     * 
     * @return void
     */
    public function test_registration()
    {
        $data = [
            'username' => 'john99',
            'email' => 'johndoe@x.com',
            'password' => '12345678',
            'password_confirmation' => '12345678'
        ];

        $nUsers_preRequest = User::count();

        $response = $this->json('POST', '/api/register', $data);
        $response->assertStatus(201);
        $this->assertEquals($nUsers_preRequest+1, User::count());
    }

    /**
     * Tests that protected routes can only be accessed when the request
     * includes a valid API token in its authorization header.
     * 
     * @return void
     */
    public function test_authenticated_user_can_access_protected_route()
    {
        $this->seed();

        $pwd = "12345678";
        $user = User::factory()->create(['password' => bcrypt($pwd)]);

        $data = [
            'email' => $user->email,
            'password' => $pwd
        ];

        $nTokens_preLogin = DB::table('personal_access_tokens')->count();

        // user logs in and server retrieves bearer token
        $response = $this->json('POST', '/api/login', $data);
        $response->assertStatus(201);
        $this->assertEquals($nTokens_preLogin+1, DB::table('personal_access_tokens')->count());
        $token = $response->json()['token'];

        // user fails to access protected route when token is not provided
        $response = $this->json('GET', '/api/some-protected-route');
        $response->assertStatus(401);

        // user successfully accesses protected route when token is provided
        $response = $this->withToken($token)->json('GET', '/api/some-protected-route');
        $response->assertStatus(200);

        // user logs out and is thus no longer authorized since token is no longer valid
        $this->withToken($token)->json('POST', '/api/logout');
        $this->assertEquals($nTokens_preLogin, DB::table('personal_access_tokens')->count());
        // *PENDING*
        // $response = $this->withToken($token)->json('GET', '/api/some-protected-route');
        // $response->assertStatus(401);
    }
}