<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Predefined realistic post topics for clear demonstration and testing
        $samplePosts = [
            [
                'title' => 'Mastering Laravel Database Migrations and Schema Builder',
                'slug' => 'mastering-laravel-database-migrations-and-schema-builder',
                'body' => 'Migrations are like version control for your database, allowing teams to define and share the application database schema definition. With Laravel Schema builder, writing cross-database compatible schemas is expressive and clean.',
                'comments' => [
                    ['author_name' => 'Alice Johnson', 'body' => 'Migrations make team development so much smoother. Great breakdown!'],
                    ['author_name' => 'Bob Martinez', 'body' => 'Cascading foreign keys on delete is crucial for data integrity.'],
                    ['author_name' => 'Charlie Lee', 'body' => 'Always remember to test rollback migrations with migrate:rollback.'],
                ],
            ],
            [
                'title' => 'Demystifying Eloquent ORM: Models and Active Record',
                'slug' => 'demystifying-eloquent-orm-models-and-active-record',
                'body' => 'Laravel Eloquent ORM provides a beautiful, simple ActiveRecord implementation for working with your database. Each database table has a corresponding Model used to interact with that table.',
                'comments' => [
                    ['author_name' => 'Diana Prince', 'body' => 'Eloquent syntax feels like natural English.'],
                    ['author_name' => 'Evan Wright', 'body' => 'Make sure to configure $fillable properly for mass assignment security.'],
                ],
            ],
            [
                'title' => 'Understanding One-to-Many Relationships in Laravel',
                'slug' => 'understanding-one-to-many-relationships-in-laravel',
                'body' => 'A one-to-many relationship is used to define relationships where a single model is the parent to one or more child models. A classic example is a blog post that has many comments.',
                'comments' => [
                    ['author_name' => 'Fiona Gallagher', 'body' => 'The hasMany and belongsTo pairing is so intuitive.'],
                    ['author_name' => 'George Clark', 'body' => 'Type-hinting HasMany and BelongsTo improves IDE autocompletion.'],
                    ['author_name' => 'Hannah Abbott', 'body' => 'Cascading deletes keep child records tidy.'],
                ],
            ],
            [
                'title' => 'Solving the N+1 Query Problem with Eager Loading',
                'slug' => 'solving-the-n-plus-1-query-problem-with-eager-loading',
                'body' => 'When accessing Eloquent relationships as properties, the relationship data is lazy loaded. This causes 1 query for the parent records, plus N additional queries for each child. Eager loading with the with() method reduces this to just 2 queries.',
                'comments' => [
                    ['author_name' => 'Ian Malcolm', 'body' => 'Eager loading transformed our response times from 800ms down to 45ms.'],
                    ['author_name' => 'Julia Roberts', 'body' => 'Always inspect queries using Laravel Telescope or DB::listen.'],
                ],
            ],
            [
                'title' => 'Filtering Database Records Efficiently with where() Clauses',
                'slug' => 'filtering-database-records-efficiently-with-where-clauses',
                'body' => 'The where method allows you to add basic WHERE clauses to your query. You can pass field names, comparison operators, and values, or chain multiple where conditions for complex filtering.',
                'comments' => [
                    ['author_name' => 'Kevin Flynn', 'body' => 'Chaining where clauses makes building dynamic search filters effortless.'],
                    ['author_name' => 'Laura Croft', 'body' => 'Remember to index columns frequently used in where clauses.'],
                ],
            ],
        ];

        // Backfill any existing posts where slug is null
        Post::whereNull('slug')->get()->each(function (Post $post) {
            $base = \Illuminate\Support\Str::slug($post->title);
            $post->slug = $base !== '' ? $base . '-' . $post->id : 'post-' . $post->id;
            $post->save();
        });

        foreach ($samplePosts as $postData) {
            $comments = $postData['comments'];
            unset($postData['comments']);

            $post = Post::firstOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );

            if ($post->comments()->count() === 0) {
                foreach ($comments as $commentData) {
                    $post->comments()->create($commentData);
                }
            }
        }

        // Generate factory posts to ensure at least 20 posts in total
        $currentCount = Post::count();
        $targetCount = 20;
        if ($currentCount < $targetCount) {
            $needed = $targetCount - $currentCount;
            for ($i = 0; $i < $needed; $i++) {
                $post = Post::factory()->create();
                Comment::factory()->count(fake()->numberBetween(2, 4))->create([
                    'post_id' => $post->id,
                ]);
            }
        }
    }
}
