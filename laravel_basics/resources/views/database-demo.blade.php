@extends('layouts.app')

@section('title', 'Database Integration & Eloquent')

@section('content')
<div class="demo-wrapper">
    <!-- Header Banner -->
    <header class="page-header">
        <div class="header-badges">
            <span class="badge badge-primary">Week 3 Day 4</span>
            <span class="badge badge-success">SQLite Database Active</span>
        </div>
        <h1 class="page-title">Database Integration & Eloquent Queries</h1>
        <p class="page-description">
            Demonstrating Laravel migrations, foreign key relationships (<code>posts</code> &harr; <code>comments</code>),
            Eloquent query builders (<code>where()</code>, <code>orderBy()</code>), and eager loading with <code>with()</code>
            to eliminate the N+1 query problem.
        </p>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number">{{ $totalPostsCount }}</span>
                <span class="stat-label">Total Seeded Posts</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">{{ $totalCommentsCount }}</span>
                <span class="stat-label">Total Seeded Comments</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">1 &rarr; N</span>
                <span class="stat-label">Post hasMany Comments</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">2 Queries</span>
                <span class="stat-label">Eager Loading Efficiency</span>
            </div>
        </div>
    </header>

    <!-- Section 1: Eager Loading with with('comments') -->
    <section class="card mb-8">
        <div class="card-header">
            <div class="icon-pill">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                    <polyline points="13 2 13 9 20 9"></polyline>
                </svg>
            </div>
            <div>
                <h2 class="card-title">1. Eager Loading with <code>with('comments')</code> (N+1 Query Prevention)</h2>
                <p class="card-subtitle">Loads posts along with their child comments in exactly 2 SQL queries regardless of record count.</p>
            </div>
        </div>

        <div class="code-banner">
            <div class="code-tag">Eloquent Query Executed:</div>
            <pre><code>$posts = Post::with('comments')->latest()->take(5)->get();</code></pre>
        </div>

        <!-- Query Log Inspector -->
        <div class="query-inspector">
            <h3 class="inspector-title">SQL Queries Executed by Laravel Query Engine ({{ count($eagerQueryLog) }} queries):</h3>
            <ol class="query-list">
                @foreach ($eagerQueryLog as $log)
                    <li class="query-item">
                        <span class="sql-badge">{{ strtoupper(explode(' ', $log['query'])[0]) }}</span>
                        <code class="sql-code">{{ $log['query'] }}</code>
                        <span class="sql-time">({{ $log['time'] }}ms)</span>
                    </li>
                @endforeach
            </ol>
            <div class="n-plus-one-note">
                <strong>Why Eager Loading Matters (N+1 Solution):</strong> Without <code>with('comments')</code>, accessing <code>$post->comments</code> inside a loop would issue 1 initial query for posts plus 1 query per post for comments (e.g. 1 + 5 = 6 queries). With eager loading, Laravel executes only <strong>2 optimized queries</strong>: one for posts, and one <code>WHERE post_id IN (...)</code> for all associated comments.
            </div>
        </div>

        <!-- Posts and Comments Display -->
        <div class="posts-stream">
            @forelse ($eagerLoadedPosts as $post)
                <article class="post-item">
                    <div class="post-meta">
                        <span class="post-id">Post #{{ $post->id }}</span>
                        <span class="post-date">{{ $post->created_at?->format('M d, Y') }}</span>
                    </div>
                    <h3 class="post-title">{{ $post->title }}</h3>
                    <p class="post-body">{{ $post->body }}</p>

                    <div class="comments-section">
                        <h4 class="comments-header">
                            <span>Comments ({{ $post->comments->count() }})</span>
                        </h4>
                        @if ($post->comments->isNotEmpty())
                            <ul class="comment-list">
                                @foreach ($post->comments as $comment)
                                    <li class="comment-item">
                                        <div class="comment-author">
                                            <strong>{{ $comment->author_name }}</strong>
                                            <span class="comment-date">{{ $comment->created_at?->diffForHumans() }}</span>
                                        </div>
                                        <p class="comment-text">{{ $comment->body }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted text-sm">No comments yet for this post.</p>
                        @endif
                    </div>
                </article>
            @empty
                <p class="text-muted">No posts found.</p>
            @endforelse
        </div>
    </section>

    <!-- Section 2: Filtering with where() -->
    <section class="card mb-8">
        <div class="card-header">
            <div class="icon-pill">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <div>
                <h2 class="card-title">2. Filtering Records with <code>where()</code></h2>
                <p class="card-subtitle">Filters database records matching specific column criteria using Eloquent WHERE clauses.</p>
            </div>
        </div>

        <div class="code-banner">
            <div class="code-tag">Eloquent Query Executed:</div>
            <pre><code>$filtered = Post::where('title', 'like', '%{{ $searchKeyword }}%')->with('comments')->get();</code></pre>
        </div>

        <form method="GET" action="{{ route('database.demo') }}" class="search-form">
            <label for="search" class="form-label">Search Post Titles with <code>where('title', 'like', ...)</code>:</label>
            <div class="search-input-group">
                <input type="text" id="search" name="search" value="{{ $searchKeyword }}" class="form-control" placeholder="Search keyword (e.g. Laravel, Query, Eloquent)...">
                <button type="submit" class="btn btn-primary">Execute Filter</button>
                <a href="{{ route('database.demo') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="results-meta">
            Showing <strong>{{ $whereFilteredPosts->count() }}</strong> post(s) matching keyword "<code>{{ $searchKeyword }}</code>":
        </div>

        <div class="filtered-cards-grid">
            @forelse ($whereFilteredPosts as $post)
                <div class="filtered-card">
                    <div class="filtered-card-header">
                        <span class="badge badge-primary">#{{ $post->id }}</span>
                        <span class="badge badge-secondary">{{ $post->comments->count() }} comments</span>
                    </div>
                    <h4 class="filtered-title">{{ $post->title }}</h4>
                    <p class="filtered-body">{{ Str::limit($post->body, 140) }}</p>
                </div>
            @empty
                <div class="empty-state">
                    <p>No posts matched your search criteria for "<strong>{{ $searchKeyword }}</strong>".</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Section 3: Sorting with orderBy() -->
    <section class="card mb-8">
        <div class="card-header">
            <div class="icon-pill">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 16 4 4 4-4"></path>
                    <path d="M7 20V4"></path>
                    <path d="m21 8-4-4-4 4"></path>
                    <path d="M17 4v16"></path>
                </svg>
            </div>
            <div>
                <h2 class="card-title">3. Sorting Records with <code>orderBy()</code></h2>
                <p class="card-subtitle">Orders Eloquent results in ascending or descending sequence.</p>
            </div>
        </div>

        <div class="code-banner">
            <div class="code-tag">Eloquent Query Executed:</div>
            <pre><code>$ordered = Post::orderBy('title', '{{ $sortDirection }}')->take(6)->get();</code></pre>
        </div>

        <div class="sort-controls">
            <span class="sort-label">Sort Direction:</span>
            <a href="{{ route('database.demo', ['direction' => 'asc', 'search' => $searchKeyword]) }}"
               class="btn {{ $sortDirection === 'asc' ? 'btn-primary' : 'btn-secondary' }}">
                Ascending (A &rarr; Z)
            </a>
            <a href="{{ route('database.demo', ['direction' => 'desc', 'search' => $searchKeyword]) }}"
               class="btn {{ $sortDirection === 'desc' ? 'btn-primary' : 'btn-secondary' }}">
                Descending (Z &rarr; A)
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Title (Ordered {{ strtoupper($sortDirection) }})</th>
                        <th>Created At</th>
                        <th style="width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderByPosts as $post)
                        <tr>
                            <td><span class="badge badge-secondary">#{{ $post->id }}</span></td>
                            <td><strong>{{ $post->title }}</strong></td>
                            <td class="text-muted">{{ $post->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('database.demo', ['search' => $post->title]) }}" class="table-action-link">
                                    Filter by Title &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- Educational Callout: Model Architecture & Foreign Key -->
    <section class="card educational-card">
        <h2 class="card-title mb-4">Architecture Summary: One-to-Many Relationship</h2>
        <div class="architecture-grid">
            <div class="arch-box">
                <h3 class="arch-box-title">Post Model (<code>App\Models\Post</code>)</h3>
                <pre class="arch-code"><code>public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}</code></pre>
                <p class="arch-desc">Enables parent-to-children navigation: <code>$post-&gt;comments</code> returns a collection of related <code>Comment</code> models.</p>
            </div>

            <div class="arch-box">
                <h3 class="arch-box-title">Comment Model (<code>App\Models\Comment</code>)</h3>
                <pre class="arch-code"><code>public function post(): BelongsTo
{
    return $this->belongsTo(Post::class);
}</code></pre>
                <p class="arch-desc">Enables child-to-parent navigation: <code>$comment-&gt;post</code> accesses the parent <code>Post</code> record.</p>
            </div>
        </div>

        <div class="foreign-key-box mt-6">
            <strong>Foreign Key Constraint:</strong>
            <code>comments.post_id &rarr; posts.id</code> with <code>onDelete('cascade')</code> ensuring that if a post is deleted, all associated comments are automatically purged to prevent orphaned records.
        </div>
    </section>
</div>

<style>
.demo-wrapper {
    max-width: 1000px;
    margin: 0 auto;
    padding-bottom: 2rem;
}
.header-badges {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.page-header {
    margin-bottom: 2rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}
.stat-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem;
    text-align: center;
    box-shadow: var(--shadow);
}
.stat-number {
    display: block;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary);
}
.stat-label {
    font-size: 0.85rem;
    color: var(--text-muted);
}
.card-subtitle {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}
.code-banner {
    background: #0f172a;
    color: #38bdf8;
    border-radius: 8px;
    padding: 1rem 1.25rem;
    margin: 1rem 0 1.25rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.9rem;
    overflow-x: auto;
}
.code-tag {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 0.25rem;
}
.query-inspector {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.inspector-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.75rem;
}
.query-list {
    margin-left: 1.25rem;
    margin-bottom: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.query-item {
    font-size: 0.85rem;
    color: #1e293b;
}
.sql-badge {
    background: #e0e7ff;
    color: #3730a3;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    margin-right: 0.4rem;
}
.sql-code {
    font-family: ui-monospace, Menlo, monospace;
    color: #0f172a;
}
.sql-time {
    color: #64748b;
    font-size: 0.75rem;
    margin-left: 0.4rem;
}
.n-plus-one-note {
    background: #ecfdf5;
    border-left: 4px solid #10b981;
    padding: 0.75rem 1rem;
    border-radius: 4px;
    font-size: 0.85rem;
    color: #065f46;
}
.posts-stream {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.post-item {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.25rem;
}
.post-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}
.post-title {
    font-size: 1.15rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.5rem;
}
.post-body {
    color: #475569;
    font-size: 0.95rem;
    margin-bottom: 1rem;
}
.comments-section {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 0.85rem 1rem;
    border-radius: 6px;
}
.comments-header {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.5rem;
}
.comment-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.comment-item {
    font-size: 0.85rem;
    border-left: 3px solid #cbd5e1;
    padding-left: 0.75rem;
}
.comment-author {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    color: #1e293b;
    font-size: 0.8rem;
}
.comment-date {
    color: #94a3b8;
    font-size: 0.75rem;
}
.comment-text {
    color: #475569;
    margin-top: 0.2rem;
}
.search-form {
    margin-bottom: 1.25rem;
}
.search-input-group {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.results-meta {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
}
.filtered-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}
.filtered-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.25rem;
}
.filtered-card-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}
.filtered-title {
    font-size: 1rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.5rem;
}
.filtered-body {
    font-size: 0.85rem;
    color: #475569;
}
.empty-state {
    padding: 2rem;
    text-align: center;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    color: var(--text-muted);
}
.sort-controls {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    margin-bottom: 1.25rem;
}
.sort-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #334155;
}
.table-responsive {
    overflow-x: auto;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}
.data-table th, .data-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.data-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
}
.table-action-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.85rem;
}
.table-action-link:hover {
    text-decoration: underline;
}
.architecture-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.25rem;
    margin-top: 1rem;
}
.arch-box {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.25rem;
}
.arch-box-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.5rem;
}
.arch-code {
    background: #0f172a;
    color: #38bdf8;
    padding: 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
    overflow-x: auto;
}
.arch-desc {
    font-size: 0.85rem;
    color: #475569;
}
.foreign-key-box {
    background: #eef2ff;
    border-left: 4px solid var(--primary);
    padding: 0.75rem 1rem;
    border-radius: 4px;
    font-size: 0.85rem;
    color: #3730a3;
}
.badge-secondary {
    background: #f1f5f9;
    color: #475569;
}
.mb-4 { margin-bottom: 1rem; }
.mb-8 { margin-bottom: 2rem; }
.mt-6 { margin-top: 1.5rem; }
</style>
@endsection
