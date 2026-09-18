<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'body',
    ];

    /**
     * The "booted" method of the model.
     * Automatically generates a slug from title if not explicitly provided.
     */
    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->slug) && !empty($post->title)) {
                $baseSlug = Str::slug($post->title);
                $post->slug = $baseSlug !== '' ? $baseSlug . '-' . Str::random(6) : 'post-' . Str::random(8);
            }
        });
    }

    /**
     * Get all comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
