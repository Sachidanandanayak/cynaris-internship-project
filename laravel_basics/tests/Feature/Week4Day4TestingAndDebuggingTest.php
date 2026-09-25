<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Week 4 Day 4 — Testing & Debugging Feature Tests
 *
 * Covers 5 foundational full-stack application feature flows:
 * A. Homepage loads successfully with HTTP 200.
 * B. User registration works and creates a user.
 * C. User login works with valid credentials.
 * D. CRUD create works for the existing Blog/Post CRUD application.
 * E. CRUD delete works for the existing Blog/Post CRUD application.
 */
class Week4Day4TestingAndDebuggingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test A: Homepage loads successfully with HTTP 200.
     */
    public function test_homepage_loads_successfully_with_http_200(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Laravel Basics - Week 3 Day 2');
        $response->assertSee('Routing Architecture');
    }

    /**
     * Test B: User registration works and creates a user.
     */
    public function test_user_registration_works_and_creates_a_user(): void
    {
        $userData = [
            'name'                  => 'Dev Tester',
            'email'                 => 'devtester@example.com',
            'phone'                 => '+1 (555) 789-0123',
            'password'              => 'secureSecret123',
            'password_confirmation' => 'secureSecret123',
        ];

        $response = $this->post(route('register'), $userData);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'name'  => 'Dev Tester',
            'email' => 'devtester@example.com',
            'phone' => '+1 (555) 789-0123',
        ]);
    }

    /**
     * Test C: User login works with valid credentials.
     */
    public function test_user_login_works_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'activeuser@example.com',
            'password' => bcrypt('correctPassword123'),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'activeuser@example.com',
            'password' => 'correctPassword123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test D: CRUD create works for the existing Blog/Post CRUD application.
     */
    public function test_crud_create_works_for_blog_post_application(): void
    {
        $postData = [
            'title' => 'Automated Testing with PHPUnit in Laravel',
            'slug'  => 'automated-testing-with-phpunit-in-laravel',
            'body'  => 'Comprehensive guide to testing controllers, forms, and models in modern Laravel applications.',
        ];

        $response = $this->post(route('blog.store'), $postData);

        $response->assertStatus(302);
        $response->assertRedirect(route('blog.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Automated Testing with PHPUnit in Laravel',
            'slug'  => 'automated-testing-with-phpunit-in-laravel',
        ]);
    }

    /**
     * Test E: CRUD delete works for the existing Blog/Post CRUD application.
     */
    public function test_crud_delete_works_for_blog_post_application(): void
    {
        $post = Post::create([
            'title' => 'Temporary Post For Deletion Verification',
            'slug'  => 'temporary-post-for-deletion-verification',
            'body'  => 'This post was instantiated to verify the HTTP DELETE resource lifecycle.',
        ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);

        $response = $this->delete(route('blog.destroy', $post));

        $response->assertStatus(302);
        $response->assertRedirect(route('blog.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
