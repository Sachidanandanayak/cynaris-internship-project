<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Remember me');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'login_user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'login_user@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_with_remember_me(): void
    {
        $user = User::factory()->create([
            'email' => 'remember_user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'remember_user@example.com',
            'password' => 'password123',
            'remember' => 'on',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
        // Verify remember_web cookie was attached
        $response->assertCookie(auth()->guard()->getRecallerName());
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'secure_user@example.com',
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'secure_user@example.com',
            'password' => 'wrong_password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_unauthenticated_users_are_redirected_to_login_for_three_protected_routes(): void
    {
        // Protected Route 1: /dashboard
        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertRedirect('/login');

        // Protected Route 2: /profile
        $profileResponse = $this->get('/profile');
        $profileResponse->assertRedirect('/login');

        // Protected Route 3: /account
        $accountResponse = $this->get('/account');
        $accountResponse->assertRedirect('/login');
    }

    public function test_authenticated_users_can_access_protected_routes(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'email_verified_at' => now(),
        ]);

        // Protected Route 1: /dashboard (verified)
        $dashResponse = $this->actingAs($user)->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Internship Workspace Dashboard');

        // Protected Route 2: /profile
        $profileResponse = $this->actingAs($user)->get('/profile');
        $profileResponse->assertStatus(200);

        // Protected Route 3: /account
        $accountResponse = $this->actingAs($user)->get('/account');
        $accountResponse->assertStatus(200);
        $accountResponse->assertSee('Account Overview & Security');
        $accountResponse->assertSee('John Doe');
        $accountResponse->assertSee('1234567890');
    }

    public function test_unverified_authenticated_user_is_redirected_from_verified_routes_to_verification_notice(): void
    {
        $unverifiedUser = User::factory()->unverified()->create();

        // /dashboard requires both 'auth' and 'verified'
        $response = $this->actingAs($unverifiedUser)->get('/dashboard');
        $response->assertRedirect(route('verification.notice'));
    }
}
