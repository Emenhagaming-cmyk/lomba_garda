<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function statefulHeaders(): array
    {
        return [
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/login',
        ];
    }

    public function test_unauthenticated_api_request_returns_json_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                ],
            ]);
    }

    public function test_unauthenticated_api_request_without_json_accept_returns_401(): void
    {
        $response = $this->get('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                ],
            ]);
    }

    public function test_registration_creates_owner_account(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Toko Sejahtera',
            'email' => 'owner@toko.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $this->statefulHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('data.user.name', 'Toko Sejahtera')
            ->assertJsonPath('data.user.role', 'OWNER');

        $this->assertDatabaseHas('users', [
            'email' => 'owner@toko.test',
            'role' => 'OWNER',
        ]);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dupe@toko.test']);

        $response = $this->postJson('/api/register', [
            'name' => 'Dupe',
            'email' => 'dupe@toko.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $this->statefulHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_login_with_wrong_credentials_returns_422(): void
    {
        User::factory()->create(['email' => 'pedagang@toko.test']);

        $response = $this->postJson('/api/login', [
            'email' => 'pedagang@toko.test',
            'password' => 'salah-password',
        ], $this->statefulHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_user_can_login_and_read_their_account(): void
    {
        User::factory()->create([
            'email' => 'pedagang@toko.test',
            'password' => 'rahasia123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'pedagang@toko.test',
            'password' => 'rahasia123',
        ], $this->statefulHeaders())->assertStatus(200);

        $this->getJson('/api/user', $this->statefulHeaders())
            ->assertStatus(200)
            ->assertJsonPath('data.user.email', 'pedagang@toko.test');
    }

    public function test_logout_returns_ok(): void
    {
        User::factory()->create([
            'email' => 'pedagang@toko.test',
            'password' => 'rahasia123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'pedagang@toko.test',
            'password' => 'rahasia123',
        ], $this->statefulHeaders())->assertStatus(200);

        $this->postJson('/api/logout', [], $this->statefulHeaders())
            ->assertStatus(200)
            ->assertJson(['data' => null]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/logout', [], $this->statefulHeaders())
            ->assertStatus(200);
    }
}
