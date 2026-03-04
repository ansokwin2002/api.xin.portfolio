<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_email_incorrect_message()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'The provided email address is incorrect.']);
    }

    public function test_login_returns_password_incorrect_message()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'The provided password is incorrect.']);
    }

    public function test_login_locks_after_3_failed_attempts()
    {
        $email = 'test@example.com';
        
        // 1st attempt
        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrong',
        ]);

        // 2nd attempt
        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrong',
        ]);

        // 3rd attempt
        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrong',
        ]);

        $response->assertStatus(429)
                 ->assertJson(['message' => 'Too many failed login attempts. You have exceeded the limit of 3 tries.']);
    }

    public function test_login_successful_clears_rate_limiter()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // 1 failed attempt
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ]);

        // Successful login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Login successful']);

        // Check 3rd attempt after success should NOT be locked immediately
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ]);
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ]);
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429)
                 ->assertJson(['message' => 'Too many failed login attempts. You have exceeded the limit of 3 tries.']);
    }
}
