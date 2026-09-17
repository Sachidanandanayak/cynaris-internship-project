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
                'body' => 'Migrations are like version control for your database, allowing teams to define and share the application database schema definition. With Laravel Schema builder, writing cross-database compatible schemas is expressive and clean.',
                'comments' => [
                    ['author_name' => 'Alice Johnson', 'body' => 'Migrations make team development so much smoother. Great breakdown!'],
                    ['author_name' => 'Bob Martinez', 'body' => 'Cascading foreign keys on delete is crucial for data integrity.'],
                    ['author_name' => 'Charlie Lee', 'body' => 'Always remember to test rollback migrations with migrate:rollback.'],
                ],
            ],
            [
                'title' => 'Demystifying Eloquent ORM: Models and Active Record',
                'body' => 'Laravel Eloquent ORM provides a beautiful, simple ActiveRecord implementation for working with your database. Each database table has a corresponding Model used to interact with that table.',
                'comments' => [
                    ['author_name' => 'Diana Prince', 'body' => 'Eloquent syntax feels like natural English.'],
                    ['author_name' => 'Evan Wright', 'body' => 'Make sure to configure $fillable properly for mass assignment security.'],
                ],
            ],
            [
                'title' => 'Understanding One-to-Many Relationships in Laravel',
                'body' => 'A one-to-many relationship is used to define relationships where a single model is the parent to one or more child models. A classic example is a blog post that has many comments.',
                'comments' => [
                    ['author_name' => 'Fiona Gallagher', 'body' => 'The hasMany and belongsTo pairing is so intuitive.'],
                    ['author_name' => 'George Clark', 'body' => 'Type-hinting HasMany and BelongsTo improves IDE autocompletion.'],
                    ['author_name' => 'Hannah Abbott', 'body' => 'Cascading deletes keep child records tidy.'],
                ],
            ],
            [
                'title' => 'Solving the N+1 Query Problem with Eager Loading',
                'body' => 'When accessing Eloquent relationships as properties, the relationship data is lazy loaded. This causes 1 query for the parent records, plus N additional queries for each child. Eager loading with the with() method reduces this to just 2 queries.',
                'comments' => [
                    ['author_name' => 'Ian Malcolm', 'body' => 'Eager loading transformed our response times from 800ms down to 45ms.'],
                    ['author_name' => 'Julia Roberts', 'body' => 'Always inspect queries using Laravel Telescope or DB::listen.'],
                ],
            ],
            [
                'title' => 'Filtering Database Records Efficiently with where() Clauses',
                'body' => 'The where method allows you to add basic WHERE clauses to your query. You can pass field names, comparison operators, and values, or chain multiple where conditions for complex filtering.',
                'comments' => [
                    ['author_name' => 'Kevin Flynn', 'body' => 'Chaining where clauses makes building dynamic search filters effortless.'],
                    ['author_name' => 'Laura Croft', 'body' => 'Remember to index columns frequently used in where clauses.'],
                ],
            ],
        ];

        foreach ($samplePosts as $postData) {
            $comments = $postData['comments'];
            unset($postData['comments']);

            $post = Post::create($postData);

            foreach ($comments as $commentData) {
                $post->comments()->create($commentData);
            }
        }

        // Generate the remaining 15 posts (total 20 posts) with factory comments
        $remainingCount = 15;
        for ($i = 0; $i < $remainingCount; $i++) {
            $post = Post::factory()->create();
            Comment::factory()->count(fake()->numberBetween(2, 4))->create([
                'post_id' => $post->id,
            ]);
        }
    }
}
