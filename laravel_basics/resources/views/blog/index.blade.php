@extends('layouts.app')

@section('title', 'Blog Posts - CRUD Application')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main);">Blog Posts</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Week 3 Day 5: Resource Routing, Form Request Validation, Pagination, and CSRF Protection.
        </p>
    </div>
    <a href="{{ route('blog.create') }}" class="btn btn-primary" id="btn-create-post">
        + Create New Post
    </a>
</div>

{{-- Session Flash Messages --}}
@if (session('success'))
    <div class="alert alert-success" role="alert" id="alert-success" style="margin-bottom: 1.5rem;">
        <strong>Success:</strong> {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-error" role="alert" id="alert-error" style="margin-bottom: 1.5rem;">
        <strong>Error:</strong> {{ session('error') }}
    </div>
@endif

{{-- Posts Data Table Card --}}
<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 2rem;">
    <div class="table-responsive">
        <table class="data-table" id="blog-posts-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Post Title & Slug</th>
                    <th>Comments</th>
                    <th>Created</th>
                    <th style="text-align: right; min-width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr id="post-row-{{ $post->id }}">
                        <td><strong>#{{ $post->id }}</strong></td>
                        <td>
                            <a href="{{ route('blog.show', $post) }}" style="text-decoration: none; color: var(--primary); font-weight: 600; font-size: 1rem;">
                                {{ $post->title }}
                            </a>
                            <div style="margin-top: 0.35rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <span style="font-family: monospace; font-size: 0.8rem; background: #e0e7ff; color: #3730a3; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                    /{{ $post->slug }}
                                </span>
                            </div>
                            <p style="font-size: 0.825rem; color: var(--text-muted); margin-top: 0.35rem; max-width: 480px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $post->body }}
                            </p>
                        </td>
                        <td>
                            <span class="badge-category" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                                {{ $post->comments_count ?? $post->comments->count() }} {{ \Illuminate\Support\Str::plural('comment', $post->comments_count ?? $post->comments->count()) }}
                            </span>
                        </td>
                        <td style="font-size: 0.825rem; color: var(--text-muted); white-space: nowrap;">
                            {{ $post->created_at ? $post->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td style="text-align: right;">
                            <div class="action-group" style="justify-content: flex-end;">
                                <a href="{{ route('blog.show', $post) }}" class="btn btn-outline btn-sm" id="btn-view-{{ $post->id }}">
                                    View
                                </a>
                                <a href="{{ route('blog.edit', $post) }}" class="btn btn-secondary btn-sm" id="btn-edit-{{ $post->id }}">
                                    Edit
                                </a>
                                <form action="{{ route('blog.destroy', $post) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete &quot;{{ addslashes($post->title) }}&quot;?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" id="btn-delete-{{ $post->id }}">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                            <p style="font-size: 1.1rem; margin-bottom: 0.75rem;">No blog posts found.</p>
                            <a href="{{ route('blog.create') }}" class="btn btn-primary btn-sm">
                                Create the First Post
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Eloquent Pagination Links --}}
    @if ($posts->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); background: #f8fafc;" id="pagination-wrapper">
            {{ $posts->links() }}
        </div>
    @endif
</div>

{{-- Architecture & Implementation Overview Card --}}
<div class="card" style="background: #f0fdf4; border-color: #bbf7d0;">
    <h3 style="font-size: 1.1rem; color: #166534; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Week 3 Day 5 Architecture & Highlights
    </h3>
    <ul style="margin-left: 1.25rem; font-size: 0.875rem; color: #14532d; line-height: 1.7;">
        <li><strong>Resource Routing:</strong> Registered via <code>Route::resource('blog', BlogPostController::class)</code> mapping all 7 RESTful actions cleanly.</li>
        <li><strong>Form Request Validation:</strong> Encapsulated in <code>BlogPostRequest</code> with <code>required</code>, <code>min</code>, <code>max</code>, and <code>unique</code> rules for title, slug, and body.</li>
        <li><strong>Route Model Binding:</strong> Automatic resolution of <code>Post $blog</code> via type-hinted controller methods with automatic 404 handling.</li>
        <li><strong>Eloquent Pagination:</strong> Paginated with <code>Post::latest()->paginate(5)</code> and rendered with <code>@{{ $posts->links() }}</code>.</li>
        <li><strong>Database Preservation:</strong> Safely extended the existing <code>posts</code> table while retaining 100% compatibility with Day 4's Post/Comment relationships.</li>
    </ul>
</div>
@endsection
