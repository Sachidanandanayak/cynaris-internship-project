<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogPostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    /**
     * Display a paginated listing of blog posts.
     *
     * Week 3 Day 5 Requirement:
     * - Eloquent pagination with Post::latest()->paginate(5)
     * - Eager load comments count to prevent N+1 query overhead
     */
    public function index(): View
    {
        $posts = Post::withCount('comments')
            ->latest()
            ->paginate(5);

        return view('blog.index', compact('posts'));
    }

    /**
     * Show the form for creating a new blog post.
     */
    public function create(): View
    {
        return view('blog.create');
    }

    /**
     * Store a newly created blog post in storage.
     *
     * Validated via BlogPostRequest FormRequest class.
     * Demonstrates safe mass assignment through Post $fillable.
     */
    public function store(BlogPostRequest $request): RedirectResponse
    {
        $post = Post::create($request->validated());

        return redirect()
            ->route('blog.index')
            ->with('success', "Blog post \"{$post->title}\" created successfully!");
    }

    /**
     * Display the specified blog post.
     *
     * Route Model Binding:
     * Automatically resolves the Post matching the {blog} parameter.
     */
    public function show(Post $blog): View
    {
        $blog->load('comments');

        return view('blog.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified blog post.
     */
    public function edit(Post $blog): View
    {
        return view('blog.edit', compact('blog'));
    }

    /**
     * Update the specified blog post in storage.
     *
     * Validated via BlogPostRequest FormRequest class.
     * Allows preserving the same slug without failing uniqueness rule.
     */
    public function update(BlogPostRequest $request, Post $blog): RedirectResponse
    {
        $blog->update($request->validated());

        return redirect()
            ->route('blog.index')
            ->with('success', "Blog post \"{$blog->title}\" updated successfully!");
    }

    /**
     * Remove the specified blog post from storage.
     */
    public function destroy(Post $blog): RedirectResponse
    {
        $title = $blog->title;
        $blog->delete();

        return redirect()
            ->route('blog.index')
            ->with('success', "Blog post \"{$title}\" was successfully deleted!");
    }
}
