@extends('layouts.app')

@section('title', $blog->title . ' - Blog')

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
    <a href="{{ route('blog.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        &larr; Back to All Posts
    </a>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('blog.edit', $blog) }}" class="btn btn-secondary btn-sm" id="btn-edit-post">
            Edit Post
        </a>
        <form action="{{ route('blog.destroy', $blog) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently delete this post?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" id="btn-delete-post">
                Delete Post
            </button>
        </form>
    </div>
</div>

{{-- Post Details Card --}}
<article class="card" style="padding: 2.5rem; margin-bottom: 2rem;">
    <header style="margin-bottom: 1.75rem; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
            <span style="font-family: monospace; font-size: 0.85rem; background: #e0e7ff; color: #3730a3; padding: 0.2rem 0.6rem; border-radius: 4px; font-weight: 600;">
                /{{ $blog->slug }}
            </span>
            <span style="font-size: 0.85rem; color: var(--text-muted);">
                Published on {{ $blog->created_at ? $blog->created_at->format('F j, Y \a\t g:i A') : 'N/A' }}
            </span>
            @if ($blog->updated_at && $blog->updated_at != $blog->created_at)
                <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                    (Updated {{ $blog->updated_at->diffForHumans() }})
                </span>
            @endif
        </div>
        <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--text-main); line-height: 1.25;">
            {{ $blog->title }}
        </h1>
    </header>

    <div style="font-size: 1.05rem; line-height: 1.8; color: #334155;">
        {!! nl2br(e($blog->body)) !!}
    </div>
</article>

{{-- Comments Section (Week 3 Day 4 Relationship Demonstration) --}}
<section class="card" style="padding: 2rem; margin-bottom: 2rem;" id="comments-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            Comments
            <span style="font-size: 0.9rem; background: #f1f5f9; color: #475569; padding: 0.15rem 0.5rem; border-radius: 9999px; font-weight: 600;">
                {{ $blog->comments->count() }}
            </span>
        </h2>
    </div>

    @if ($blog->comments->count() > 0)
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            @foreach ($blog->comments as $comment)
                <div style="padding: 1.25rem; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <strong style="font-size: 0.95rem; color: var(--text-main);">
                            {{ $comment->author_name }}
                        </strong>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ $comment->created_at ? $comment->created_at->diffForHumans() : 'Recently' }}
                        </span>
                    </div>
                    <p style="font-size: 0.9rem; color: #475569; margin: 0; line-height: 1.5;">
                        {{ $comment->body }}
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
            <p>No comments on this post yet.</p>
        </div>
    @endif
</section>

{{-- Technical Architecture Callout --}}
<div class="card" style="background: #eff6ff; border-color: #bfdbfe;">
    <h3 style="font-size: 1.05rem; color: #1e40af; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        Route Model Binding &amp; Eloquent Integration
    </h3>
    <p style="font-size: 0.875rem; color: #1e3a8a; line-height: 1.6; margin: 0;">
        This page was resolved via Laravel's <strong>Route Model Binding</strong> using <code>show(Post $blog)</code>. The framework automatically queried <code>Post::findOrFail($id)</code> based on the route parameter <code>{blog}</code>. The associated comments were loaded using Eloquent's <code>$blog->load('comments')</code>, demonstrating complete continuity with the Day 4 Post/Comment schema.
    </p>
</div>
@endsection
