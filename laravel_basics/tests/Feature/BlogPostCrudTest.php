<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test GET /blog displays blog index page.
     */
    public function test_blog_index_displays_posts_list(): void
    {
        $post = Post::create([
            'title' => 'Mastering Laravel Service Container',
            'slug'  => 'mastering-laravel-service-container',
            'body'  => 'Deep dive into inversion of control and dependency injection.',
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertStatus(200);
        $response->assertViewIs('blog.index');
        $response->assertSee('Blog Posts');
        $response->assertSee('Mastering Laravel Service Container');
        $response->assertSee('/mastering-laravel-service-container');
    }

    /**
     * Test GET /blog displays pagination links when posts exceed page size.
     */
    public function test_blog_index_displays_posts_with_pagination(): void
    {
        // Create 12 posts (pagination size is 5, so 3 pages)
        Post::factory()->count(12)->create();

        $response = $this->get(route('blog.index'));

        $response->assertStatus(200);
        $response->assertViewHas('posts');

        $posts = $response->viewData('posts');
        $this->assertCount(5, $posts);
        $this->assertEquals(12, $posts->total());
        $this->assertEquals(3, $posts->lastPage());

        // Test second page
        $page2Response = $this->get(route('blog.index', ['page' => 2]));
        $page2Response->assertStatus(200);
        $page2Posts = $page2Response->viewData('posts');
        $this->assertCount(5, $page2Posts);
    }

    /**
     * Test GET /blog/create displays creation form.
     */
    public function test_blog_create_page_renders_successfully(): void
    {
        $response = $this->get(route('blog.create'));

        $response->assertStatus(200);
        $response->assertViewIs('blog.create');
        $response->assertSee('Create New Blog Post');
        $response->assertSee('create-post-form');
        $response->assertSee('URL Slug');
    }

    /**
     * Test POST /blog creates post and redirects to index with success flash.
     */
    public function test_valid_blog_post_can_be_created(): void
    {
        $payload = [
            'title' => 'Understanding Laravel Eloquent Events',
            'slug'  => 'understanding-laravel-eloquent-events',
            'body'  => 'Eloquent models fire several events allowing you to hook into model lifecycle.',
        ];

        $response = $this->post(route('blog.store'), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('blog.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Understanding Laravel Eloquent Events',
            'slug'  => 'understanding-laravel-eloquent-events',
        ]);
    }

    /**
     * Test POST /blog validates required fields.
     */
    public function test_blog_post_creation_requires_title_slug_and_body(): void
    {
        $response = $this->post(route('blog.store'), [
            'title' => '',
            'slug'  => '',
            'body'  => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title', 'slug', 'body']);
        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * Test POST /blog validates min and max character constraints.
     */
    public function test_blog_post_validates_min_and_max_lengths(): void
    {
        $response = $this->post(route('blog.store'), [
            'title' => 'AB', // min 3 required
            'slug'  => 'ab', // min 3 required
            'body'  => 'Short', // min 10 required
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title', 'slug', 'body']);

        // Test max length on title & slug (max 255)
        $oversizedResponse = $this->post(route('blog.store'), [
            'title' => str_repeat('A', 256),
            'slug'  => str_repeat('a', 256),
            'body'  => 'Valid length body content.',
        ]);

        $oversizedResponse->assertStatus(302);
        $oversizedResponse->assertSessionHasErrors(['title', 'slug']);
    }

    /**
     * Test POST /blog enforces unique slug rule.
     */
    public function test_blog_post_slug_must_be_unique_on_creation(): void
    {
        Post::create([
            'title' => 'Existing Unique Post',
            'slug'  => 'existing-unique-post',
            'body'  => 'Existing post content that occupies this slug.',
        ]);

        $response = $this->post(route('blog.store'), [
            'title' => 'Duplicate Slug Post Attempt',
            'slug'  => 'existing-unique-post', // Duplicate slug
            'body'  => 'This should fail validation because slug is not unique.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);
        $this->assertDatabaseCount('posts', 1);
    }

    /**
     * Test GET /blog/{blog} displays post details and associated comments.
     */
    public function test_blog_show_page_displays_post_and_comments(): void
    {
        $post = Post::create([
            'title' => 'Deep Dive into Laravel Middleware',
            'slug'  => 'deep-dive-into-laravel-middleware',
            'body'  => 'Middleware provides a convenient mechanism for inspecting and filtering HTTP requests.',
        ]);

        Comment::create([
            'post_id'     => $post->id,
            'author_name' => 'Reviewer Jane',
            'body'        => 'Exceptional explanation of middleware pipelines!',
        ]);

        $response = $this->get(route('blog.show', $post));

        $response->assertStatus(200);
        $response->assertViewIs('blog.show');
        $response->assertSee('Deep Dive into Laravel Middleware');
        $response->assertSee('/deep-dive-into-laravel-middleware');
        $response->assertSee('Reviewer Jane');
        $response->assertSee('Exceptional explanation of middleware pipelines!');
    }

    /**
     * Test GET /blog/{blog}/edit renders pre-filled edit form.
     */
    public function test_blog_edit_page_renders_with_existing_data(): void
    {
        $post = Post::create([
            'title' => 'Drafting Microservices Architecture',
            'slug'  => 'drafting-microservices-architecture',
            'body'  => 'Architectural patterns for decoupled enterprise systems.',
        ]);

        $response = $this->get(route('blog.edit', $post));

        $response->assertStatus(200);
        $response->assertViewIs('blog.edit');
        $response->assertSee('Edit Blog Post');
        $response->assertSee('Drafting Microservices Architecture');
        $response->assertSee('drafting-microservices-architecture');
    }

    /**
     * Test PUT /blog/{blog} updates post and allows preserving the exact same slug.
     */
    public function test_blog_post_can_be_updated_keeping_same_slug(): void
    {
        $post = Post::create([
            'title' => 'Initial Title Before Revision',
            'slug'  => 'revisable-post-slug',
            'body'  => 'Initial post body before editorial updates.',
        ]);

        $response = $this->put(route('blog.update', $post), [
            'title' => 'Updated Title After Revision',
            'slug'  => 'revisable-post-slug', // Same slug preserved
            'body'  => 'Updated and expanded body content after editorial revision.',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('blog.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'id'    => $post->id,
            'title' => 'Updated Title After Revision',
            'slug'  => 'revisable-post-slug',
            'body'  => 'Updated and expanded body content after editorial revision.',
        ]);
    }

    /**
     * Test PUT /blog/{blog} fails if slug is changed to an existing slug of another post.
     */
    public function test_blog_post_update_fails_if_slug_collides_with_another_post(): void
    {
        $postA = Post::create([
            'title' => 'First Post Alpha',
            'slug'  => 'first-post-alpha',
            'body'  => 'Body content for the alpha post record.',
        ]);

        $postB = Post::create([
            'title' => 'Second Post Beta',
            'slug'  => 'second-post-beta',
            'body'  => 'Body content for the beta post record.',
        ]);

        // Try to update post B to use post A's slug
        $response = $this->put(route('blog.update', $postB), [
            'title' => 'Second Post Renamed',
            'slug'  => 'first-post-alpha', // Taken by postA
            'body'  => 'Body content attempted with collision.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);

        $this->assertDatabaseHas('posts', [
            'id'    => $postB->id,
            'slug'  => 'second-post-beta',
        ]);
    }

    /**
     * Test DELETE /blog/{blog} deletes the post and redirects.
     */
    public function test_blog_post_can_be_deleted(): void
    {
        $post = Post::create([
            'title' => 'Temporary Post for Deletion',
            'slug'  => 'temporary-post-for-deletion',
            'body'  => 'This record should be removed from the database.',
        ]);

        $response = $this->delete(route('blog.destroy', $post));

        $response->assertStatus(302);
        $response->assertRedirect(route('blog.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * Test CSRF protection is required on state-changing requests.
     */
    public function test_csrf_token_is_present_in_create_and_edit_forms(): void
    {
        $post = Post::create([
            'title' => 'CSRF Verification Post',
            'slug'  => 'csrf-verification-post',
            'body'  => 'Checking that CSRF hidden input tokens exist in blade forms.',
        ]);

        $createResponse = $this->get(route('blog.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('name="_token"', false);

        $editResponse = $this->get(route('blog.edit', $post));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('name="_token"', false);
        $editResponse->assertSee('name="_method" value="PUT"', false);
    }
}
