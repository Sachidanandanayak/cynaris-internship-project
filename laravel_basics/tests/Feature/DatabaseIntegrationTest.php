<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use Database\Seeders\PostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test posts table can persist and retrieve records using Eloquent Post model.
     */
    public function test_posts_table_can_persist_and_retrieve_records(): void
    {
        $post = Post::create([
            'title' => 'Building High-Performance Databases in Laravel',
            'body' => 'Deep dive into database indexing, eager loading, and query caching.',
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Building High-Performance Databases in Laravel',
        ]);

        $retrieved = Post::find($post->id);
        $this->assertNotNull($retrieved);
        $this->assertEquals('Building High-Performance Databases in Laravel', $retrieved->title);
        $this->assertEquals('Deep dive into database indexing, eager loading, and query caching.', $retrieved->body);
    }

    /**
     * Test comments table persists records with proper foreign key.
     */
    public function test_comments_table_persists_records_with_foreign_key(): void
    {
        $post = Post::create([
            'title' => 'Database Foreign Key Integrity',
            'body' => 'Testing foreign key constraints and schema relations.',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'author_name' => 'Sarah Connor',
            'body' => 'Foreign keys keep our database consistent.',
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'post_id' => $post->id,
            'author_name' => 'Sarah Connor',
        ]);
    }

    /**
     * Test Post hasMany Comment relationship.
     */
    public function test_post_has_many_comments_relationship(): void
    {
        $post = Post::create([
            'title' => 'One-to-Many Architecture Test',
            'body' => 'Testing hasMany relation between Post and Comments.',
        ]);

        $post->comments()->createMany([
            ['author_name' => 'Reviewer One', 'body' => 'First insightful thought.'],
            ['author_name' => 'Reviewer Two', 'body' => 'Second follow-up critique.'],
            ['author_name' => 'Reviewer Three', 'body' => 'Third supporting remark.'],
        ]);

        $this->assertCount(3, $post->comments);
        $this->assertInstanceOf(Comment::class, $post->comments->first());
        $this->assertEquals('Reviewer One', $post->comments->first()->author_name);
    }

    /**
     * Test Comment belongsTo Post relationship.
     */
    public function test_comment_belongs_to_post_relationship(): void
    {
        $post = Post::create([
            'title' => 'Belongs-To Relationship Test',
            'body' => 'Verifying inverse relationship from child to parent.',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'author_name' => 'Alex Turner',
            'body' => 'Testing post retrieval from comment.',
        ]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($post->id, $comment->post->id);
        $this->assertEquals('Belongs-To Relationship Test', $comment->post->title);
    }

    /**
     * Test cascading delete removes comments when parent post is deleted.
     */
    public function test_deleting_post_cascades_and_removes_associated_comments(): void
    {
        $post = Post::create([
            'title' => 'Post Pending Deletion',
            'body' => 'Post with child comments that should be cascade deleted.',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'author_name' => 'Ephemeral User',
            'body' => 'This comment should vanish when post is deleted.',
        ]);

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);

        $post->delete();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /**
     * Test Eloquent where() query filters records accurately.
     */
    public function test_where_query_filters_records_accurately(): void
    {
        Post::create(['title' => 'Laravel MVC Architecture Patterns', 'body' => 'Content A']);
        Post::create(['title' => 'React Frontend State Management', 'body' => 'Content B']);
        Post::create(['title' => 'Mastering Laravel Database Queries', 'body' => 'Content C']);

        $laravelPosts = Post::where('title', 'like', '%Laravel%')->get();
        $reactPosts = Post::where('title', 'like', '%React%')->get();
        $vuePosts = Post::where('title', 'like', '%Vue%')->get();

        $this->assertCount(2, $laravelPosts);
        $this->assertCount(1, $reactPosts);
        $this->assertCount(0, $vuePosts);
    }

    /**
     * Test Eloquent orderBy() query sorts records correctly.
     */
    public function test_order_by_query_sorts_records_correctly(): void
    {
        Post::create(['title' => 'Bravo Post', 'body' => 'Body B']);
        Post::create(['title' => 'Alpha Post', 'body' => 'Body A']);
        Post::create(['title' => 'Charlie Post', 'body' => 'Body C']);

        $ascending = Post::orderBy('title', 'asc')->pluck('title')->all();
        $this->assertEquals(['Alpha Post', 'Bravo Post', 'Charlie Post'], $ascending);

        $descending = Post::orderBy('title', 'desc')->pluck('title')->all();
        $this->assertEquals(['Charlie Post', 'Bravo Post', 'Alpha Post'], $descending);
    }

    /**
     * Test eager loading with('comments') loads relationships in exactly 2 SQL queries (N+1 prevention).
     */
    public function test_eager_loading_with_comments_loads_relations_in_two_queries(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $post = Post::create([
                'title' => "Batch Post {$i}",
                'body' => "Body for batch post {$i}",
            ]);
            $post->comments()->create([
                'author_name' => "Author {$i}",
                'body' => "Comment for batch post {$i}",
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $posts = Post::with('comments')->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Exactly 2 queries: one for posts table, one for comments table WHERE post_id IN (...)
        $this->assertCount(2, $queries);
        $this->assertTrue($posts->first()->relationLoaded('comments'));
        $this->assertNotEmpty($posts->first()->comments);
    }

    /**
     * Test PostFactory and CommentFactory instantiate valid models.
     */
    public function test_post_and_comment_factories_generate_valid_models(): void
    {
        $post = Post::factory()->create();
        $this->assertNotNull($post->title);
        $this->assertNotNull($post->body);

        $comment = Comment::factory()->create(['post_id' => $post->id]);
        $this->assertEquals($post->id, $comment->post_id);
        $this->assertNotNull($comment->author_name);
        $this->assertNotNull($comment->body);
    }

    /**
     * Test PostSeeder seeds at least 20 posts and multiple comments.
     */
    public function test_post_seeder_seeds_at_least_twenty_posts_and_multiple_comments(): void
    {
        $this->seed(PostSeeder::class);

        $postCount = Post::count();
        $commentCount = Comment::count();

        $this->assertGreaterThanOrEqual(20, $postCount);
        $this->assertGreaterThanOrEqual(20, $commentCount);
    }

    /**
     * Test GET /database-demo page renders successfully and displays query components.
     */
    public function test_database_demo_page_renders_successfully(): void
    {
        $post = Post::create([
            'title' => 'Laravel Eloquent Deep Dive',
            'body' => 'Comprehensive demonstration of database integration.',
        ]);
        $post->comments()->create([
            'author_name' => 'Demo User',
            'body' => 'Great demonstration of eager loading.',
        ]);

        $response = $this->get(route('database.demo'));

        $response->assertStatus(200);
        $response->assertSee('Database Integration & Eloquent Queries', false);
        $response->assertSee('Eager Loading with');
        $response->assertSee('Filtering Records with');
        $response->assertSee('Sorting Records with');
        $response->assertSee('Laravel Eloquent Deep Dive');
        $response->assertSee('Demo User');
    }

    /**
     * Test GET /database-demo with search and sort parameters.
     */
    public function test_database_demo_search_and_sort_parameters_work(): void
    {
        Post::create([
            'title' => 'Kubernetes Pod Networking',
            'body' => 'Container orchestration and networking.',
        ]);
        Post::create([
            'title' => 'Laravel Queue Workers with Redis',
            'body' => 'Background job processing.',
        ]);

        $response = $this->get(route('database.demo', ['search' => 'Kubernetes', 'direction' => 'desc']));

        $response->assertStatus(200);
        $response->assertSee('Kubernetes Pod Networking');
        $response->assertSee('Showing <strong>1</strong> post(s)', false);
    }
}
