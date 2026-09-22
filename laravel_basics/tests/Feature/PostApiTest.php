<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: GET /api/posts collection returns 200 OK with PostResource structure.
     */
    public function test_can_get_posts_collection_with_api_resource_structure(): void
    {
        $post1 = Post::create([
            'title' => 'First API Post',
            'slug'  => 'first-api-post',
            'body'  => 'This is the body content for the first API post testing.',
        ]);

        $post2 = Post::create([
            'title' => 'Second API Post',
            'slug'  => 'second-api-post',
            'body'  => 'This is the body content for the second API post testing.',
        ]);

        Comment::create([
            'post_id'     => $post1->id,
            'author_name' => 'Alice Developer',
            'body'        => 'Insightful article on Laravel APIs!',
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'slug',
                    'body',
                    'comments_count',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta'  => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
        ]);

        $response->assertJsonFragment([
            'title'          => 'First API Post',
            'comments_count' => 1,
        ]);
    }

    /**
     * Test 2: GET /api/posts/{id} returns single post with 200 OK.
     */
    public function test_can_get_single_post_by_id(): void
    {
        $post = Post::create([
            'title' => 'Deep Dive into API Resources',
            'slug'  => 'deep-dive-into-api-resources',
            'body'  => 'Explaining how JsonResource standardizes Laravel API outputs.',
        ]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'slug',
                'body',
                'comments_count',
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'id'    => $post->id,
                'title' => 'Deep Dive into API Resources',
                'slug'  => 'deep-dive-into-api-resources',
            ],
        ]);
    }

    /**
     * Test 3: GET /api/posts/{id} returns 404 JSON when resource does not exist.
     */
    public function test_returns_404_when_post_not_found(): void
    {
        $response = $this->getJson('/api/posts/99999');

        $response->assertStatus(404);
    }

    /**
     * Test 4: POST /api/posts returns 401 Unauthorized for unauthenticated requests.
     */
    public function test_unauthenticated_access_is_rejected_on_create(): void
    {
        $payload = [
            'title' => 'Unauthorized Post Attempt',
            'body'  => 'This should fail because no Sanctum token was provided in headers.',
        ];

        $response = $this->postJson('/api/posts', $payload);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('posts', ['title' => 'Unauthorized Post Attempt']);
    }

    /**
     * Test 5: POST /api/posts creates post and returns 201 Created with Sanctum token.
     */
    public function test_authenticated_user_with_sanctum_can_create_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'title' => 'Authenticated Post via Sanctum',
            'slug'  => 'authenticated-post-via-sanctum',
            'body'  => 'Successfully stored through protected API route with Bearer token.',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/posts', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'title', 'slug', 'body', 'created_at', 'updated_at'],
        ]);
        $response->assertJson([
            'data' => [
                'title' => 'Authenticated Post via Sanctum',
                'slug'  => 'authenticated-post-via-sanctum',
            ],
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Authenticated Post via Sanctum',
            'slug'  => 'authenticated-post-via-sanctum',
        ]);
    }

    /**
     * Test 6: POST /api/posts returns 422 Unprocessable Entity on validation failure.
     */
    public function test_post_creation_validation_failure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Title and body missing
        $response = $this->postJson('/api/posts', [
            'title' => '',
            'body'  => 'Too short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'body']);
    }

    /**
     * Test 7: PUT /api/posts/{id} returns 401 Unauthorized for unauthenticated requests.
     */
    public function test_unauthenticated_access_is_rejected_on_update(): void
    {
        $post = Post::create([
            'title' => 'Original Post Title',
            'slug'  => 'original-post-title',
            'body'  => 'Original body content before attempted modification.',
        ]);

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Hacked Post Title',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseHas('posts', ['title' => 'Original Post Title']);
    }

    /**
     * Test 8: PUT /api/posts/{id} updates post and returns 200 OK with Sanctum.
     */
    public function test_authenticated_user_can_update_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $post = Post::create([
            'title' => 'Initial Title Before Update',
            'slug'  => 'initial-title-before-update',
            'body'  => 'Initial content that will be updated through the REST API.',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/posts/{$post->id}", [
                'title' => 'Updated Post Title via REST API',
                'body'  => 'Updated body content that meets the minimum length requirement.',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id'    => $post->id,
                'title' => 'Updated Post Title via REST API',
            ],
        ]);

        $this->assertDatabaseHas('posts', [
            'id'    => $post->id,
            'title' => 'Updated Post Title via REST API',
        ]);
    }

    /**
     * Test 9: PUT /api/posts/{id} returns 422 Unprocessable Entity on validation failure.
     */
    public function test_post_update_validation_failure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::create([
            'title' => 'Valid Initial Title',
            'slug'  => 'valid-initial-title',
            'body'  => 'Valid initial body content for validation test.',
        ]);

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'No', // Too short (min: 3)
            'body'  => 'Short', // Too short (min: 10)
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'body']);
    }

    /**
     * Test 10: DELETE /api/posts/{id} returns 401 Unauthorized for unauthenticated requests.
     */
    public function test_unauthenticated_access_is_rejected_on_delete(): void
    {
        $post = Post::create([
            'title' => 'Protected From Delete',
            'slug'  => 'protected-from-delete',
            'body'  => 'This post should not be deleted by an unauthenticated request.',
        ]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    /**
     * Test 11: DELETE /api/posts/{id} deletes post and returns 200 OK with Sanctum.
     */
    public function test_authenticated_user_can_delete_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $post = Post::create([
            'title' => 'Post to be Deleted',
            'slug'  => 'post-to-be-deleted',
            'body'  => 'This post will be deleted by the authenticated API request.',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Post deleted successfully.',
        ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    /**
     * Test 12: POST /api/login issues Sanctum Bearer token on valid credentials.
     */
    public function test_sanctum_login_issues_token_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'api-tester@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'       => 'api-tester@example.com',
            'password'    => 'secret123',
            'device_name' => 'PHPUnit Test Runner',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
            'token_type',
            'user' => ['id', 'name', 'email'],
        ]);
        $response->assertJson([
            'token_type' => 'Bearer',
            'user' => [
                'id'    => $user->id,
                'email' => 'api-tester@example.com',
            ],
        ]);

        $token = $response->json('token');
        $this->assertNotEmpty($token);
    }

    /**
     * Test 13: POST /api/login rejects invalid credentials with 401.
     */
    public function test_sanctum_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'valid@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'valid@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure(['message', 'errors']);
    }

    /**
     * Test 14: Authenticated user can fetch /api/user and revoke token via /api/logout.
     */
    public function test_sanctum_authenticated_user_profile_and_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('temp-session')->plainTextToken;

        // Profile check
        $profileResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');

        $profileResponse->assertStatus(200);
        $profileResponse->assertJson([
            'user' => [
                'id'    => $user->id,
                'email' => $user->email,
            ],
        ]);

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200);
        $logoutResponse->assertJson([
            'message' => 'Token revoked successfully.',
        ]);

        // After revocation, token cannot be used
        $this->app['auth']->forgetGuards();
        $subsequentResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');

        $subsequentResponse->assertStatus(401);
    }

    /**
     * Test 15: Frontend API demo page /api-demo returns 200 OK.
     */
    public function test_frontend_api_demo_page_loads_successfully(): void
    {
        $response = $this->get('/api-demo');

        $response->assertStatus(200);
        $response->assertSee('RESTful API &amp; Laravel Sanctum Demo', false);
        $response->assertSee('Sanctum Token Manager');
        $response->assertSee('Live API Inspector');
    }
}
