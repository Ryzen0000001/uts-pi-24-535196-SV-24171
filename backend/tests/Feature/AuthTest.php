<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_endpoint_is_not_exposed(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Rizendy',
            'email' => 'rizendy@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertNotFound();
    }

    public function test_login_with_invalid_email_returns_422(): void
    {
        $this->postJson('/api/login', [
            'email' => 'bukan-email',
            'password' => 'rahasia123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_login_with_valid_credentials_returns_token(): void
    {
        User::factory()->create([
            'email' => 'rizendy@example.com',
            'password' => Hash::make('rahasia123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'rizendy@example.com',
            'password' => 'rahasia123',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        User::factory()->create([
            'email' => 'rizendy@example.com',
            'password' => Hash::make('rahasia123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'rizendy@example.com',
            'password' => 'salah-total',
        ])->assertUnauthorized();
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_me_without_token_returns_401(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }
}
