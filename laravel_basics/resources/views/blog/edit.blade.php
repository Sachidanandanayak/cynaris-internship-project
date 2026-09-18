@extends('layouts.app')

@section('title', 'Edit Post: ' . $blog->title . ' - CRUD Application')

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('blog.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        &larr; Back to Blog Posts
    </a>
    <a href="{{ route('blog.show', $blog) }}" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">
        View Post &rarr;
    </a>
</div>

<div class="card" style="max-width: 760px; margin: 0 auto; padding: 2.25rem;">
    <div style="margin-bottom: 1.75rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem;">
            <span style="font-family: monospace; font-size: 0.85rem; background: #f1f5f9; color: #475569; padding: 0.2rem 0.5rem; border-radius: 4px;">
                ID #{{ $blog->id }}
            </span>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0;">
                Edit Blog Post
            </h1>
        </div>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Update this post record. Demonstrates Form Request validation with unique slug ignoring the current record ID, HTTP method spoofing (<code>@method('PUT')</code>), and CSRF protection.
        </p>
    </div>

    {{-- Validation Error Summary Alert --}}
    @if ($errors->any())
        <div class="alert alert-error" role="alert" id="validation-errors-alert" style="margin-bottom: 1.75rem;">
            <strong>Please resolve the following errors:</strong>
            <ul style="margin-left: 1.25rem; margin-top: 0.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('blog.update', $blog) }}" method="POST" id="edit-post-form" novalidate>
        {{-- CSRF Protection Directive --}}
        @csrf

        {{-- HTTP Method Spoofing for RESTful PUT --}}
        @method('PUT')

        {{-- Post Title Field --}}
        <div class="form-group">
            <label for="title" class="form-label">
                Post Title <span style="color: #ef4444;">*</span>
            </label>
            <input
                type="text"
                id="title"
                name="title"
                class="form-control @error('title') is-invalid @enderror"
                placeholder="Post title"
                value="{{ old('title', $blog->title) }}"
                required
            >
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.25rem;">
                Minimum 3 characters, maximum 255 characters.
            </small>
        </div>

        {{-- URL Slug Field --}}
        <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                <label for="slug" class="form-label" style="margin-bottom: 0;">
                    URL Slug <span style="color: #ef4444;">*</span>
                </label>
                <button
                    type="button"
                    id="btn-auto-slug"
                    style="background: none; border: none; color: var(--primary); font-size: 0.8rem; cursor: pointer; text-decoration: underline; padding: 0;"
                    onclick="generateSlug()"
                >
                    Regenerate from title
                </button>
            </div>
            <input
                type="text"
                id="slug"
                name="slug"
                class="form-control @error('slug') is-invalid @enderror"
                placeholder="url-slug"
                value="{{ old('slug', $blog->slug) }}"
                required
            >
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.25rem;">
                Unique URL identifier (Rule::unique ignores this post's current ID: #{{ $blog->id }}).
            </small>
        </div>

        {{-- Post Content Body Field --}}
        <div class="form-group">
            <label for="body" class="form-label">
                Post Content <span style="color: #ef4444;">*</span>
            </label>
            <textarea
                id="body"
                name="body"
                rows="8"
                class="form-control @error('body') is-invalid @enderror"
                placeholder="Write your blog post body here..."
                required
            >{{ old('body', $blog->body) }}</textarea>
            @error('body')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.25rem;">
                Minimum 10 characters.
            </small>
        </div>

        {{-- Form Actions --}}
        <div style="display: flex; gap: 1rem; align-items: center; margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" id="btn-update-post">
                Save Changes
            </button>
            <a href="{{ route('blog.show', $blog) }}" class="btn btn-outline">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    function generateSlug() {
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        if (!titleInput || !slugInput) return;

        slugInput.value = titleInput.value
            .toLowerCase()
            .trim()
            .replace(/[\s\W-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
</script>
@endsection
