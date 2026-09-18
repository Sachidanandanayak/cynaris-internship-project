@extends('layouts.app')

@section('title', 'Create Blog Post - CRUD Application')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('blog.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        &larr; Back to Blog Posts
    </a>
</div>

<div class="card" style="max-width: 760px; margin: 0 auto; padding: 2.25rem;">
    <div style="margin-bottom: 1.75rem;">
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem;">
            Create New Blog Post
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Publish a new article. Demonstrates Form Request validation (<code>required</code>, <code>min</code>, <code>max</code>, <code>unique</code>), CSRF protection, and old input persistence.
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

    <form action="{{ route('blog.store') }}" method="POST" id="create-post-form" novalidate>
        {{-- CSRF Protection Directive --}}
        @csrf

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
                placeholder="e.g. Architecting Scalable REST APIs in Laravel 11"
                value="{{ old('title') }}"
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
                    Generate from title
                </button>
            </div>
            <input
                type="text"
                id="slug"
                name="slug"
                class="form-control @error('slug') is-invalid @enderror"
                placeholder="e.g. architecting-scalable-rest-apis-in-laravel-11"
                value="{{ old('slug') }}"
                required
            >
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.25rem;">
                Must be unique, lowercase alphanumeric with hyphens/underscores (3–255 characters).
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
            >{{ old('body') }}</textarea>
            @error('body')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.25rem;">
                Minimum 10 characters.
            </small>
        </div>

        {{-- Form Submission Actions --}}
        <div style="display: flex; gap: 1rem; align-items: center; margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" id="btn-submit-post">
                Publish Blog Post
            </button>
            <a href="{{ route('blog.index') }}" class="btn btn-outline">
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

        const slug = titleInput.value
            .toLowerCase()
            .trim()
            .replace(/[\s\W-]+/g, '-')
            .replace(/^-+|-+$/g, '');

        slugInput.value = slug;
    }

    // Auto-generate slug when title changes if slug is empty
    document.getElementById('title')?.addEventListener('input', function() {
        const slugInput = document.getElementById('slug');
        if (slugInput && !slugInput.dataset.manual) {
            slugInput.value = this.value
                .toLowerCase()
                .trim()
                .replace(/[\s\W-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });

    document.getElementById('slug')?.addEventListener('input', function() {
        this.dataset.manual = "true";
    });
</script>
@endsection
