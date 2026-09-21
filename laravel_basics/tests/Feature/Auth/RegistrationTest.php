<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Phone Number');
    }

    public function test_new_users_can_register_with_valid_data_including_phone_number(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'phone' => '+1 (555) 234-5678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'phone' => '+1 (555) 234-5678',
        ]);
    }

    public function test_registration_fails_without_phone_number(): void
    {
        $response = $this->post('/register', [
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_registration_fails_with_invalid_phone_number(): void
    {
        $response = $this->post('/register', [
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'phone' => 'abc-not-a-phone!',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->post('/register', [
            'name' => 'Duplicate User',
            'email' => 'duplicate@example.com',
            'phone' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Dave Miller',
            'email' => 'dave@example.com',
            'phone' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}
