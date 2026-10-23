<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->owner()->create(['email' => 'owner@test.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email', 'role']])
            ->assertJsonPath('user.role', 'owner')
            ->assertJsonMissingPath('user.password');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'user@test.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'user@test.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_login_fails_for_unknown_email(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'nobody@test.com',
            'password' => 'password',
        ])->assertUnauthorized();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_protected_endpoints_reject_unauthenticated_requests(): void
    {
        $this->getJson('/api/items')->assertUnauthorized();
        $this->getJson('/api/customers')->assertUnauthorized();
    }

    public function test_logout_revokes_the_token(): void
    {
        User::factory()->create(['email' => 'user@test.com']);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'user@test.com',
            'password' => 'password',
        ])->json('token');

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        // The same token must no longer work. Reset the auth guard cache
        // so the second request is authenticated from scratch.
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/items')->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}