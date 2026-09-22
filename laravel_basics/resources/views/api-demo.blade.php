@extends('layouts.app')

@section('title', 'API Integration & Sanctum Demo')

@section('content')
<div class="container" style="max-width: 1100px; margin-top: 1rem; margin-bottom: 3rem;">

    {{-- Hero Section --}}
    <div class="hero" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); padding: 2.25rem 2rem; border-radius: var(--radius); margin-bottom: 2rem; box-shadow: var(--shadow);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: rgba(255,255,255,0.15); border-radius: 9999px; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 0.75rem;">
                    Week 4 Day 2 Feature
                </span>
                <h1 style="font-size: 2rem; font-weight: 800; line-height: 1.2; margin-bottom: 0.5rem; color: #ffffff;">
                    RESTful API &amp; Laravel Sanctum Demo
                </h1>
                <p style="font-size: 1rem; color: #c7d2fe; max-width: 680px; line-height: 1.5;">
                    Live demonstration of consuming the Laravel RESTful Post API using <code>fetch()</code>. Tests public reads, Sanctum Bearer token generation, protected write operations (POST, PUT, DELETE), validation errors (422), unauthenticated states (401), and route model binding.
                </p>
            </div>
            <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); padding: 1rem 1.25rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); min-width: 240px;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: #cbd5e1; font-weight: 700; margin-bottom: 0.25rem;">Sanctum Token Status</div>
                <div id="authStatusBadge" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.95rem; color: #fca5a5;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                    Unauthenticated
                </div>
            </div>
        </div>
    </div>

    {{-- Architecture & Documentation Banner --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; color: #4338ca; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.25rem;">1. REST Endpoints</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">5 endpoints covering Index, Show, Store, Update, Destroy via <code>PostApiController</code> &amp; <code>PostResource</code>.</p>
        </div>
        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; color: #0284c7; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.25rem;">2. Sanctum Auth</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Bearer token authentication on <code>POST</code>, <code>PUT</code>, <code>DELETE</code>. Public reads for <code>GET</code>.</p>
        </div>
        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; color: #059669; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.25rem;">3. Secure CORS</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Configured with explicit trusted origins (no insecure <code>*</code> wildcards) &amp; credential support.</p>
        </div>
    </div>

    {{-- Section 1: Sanctum Token Generator --}}
    <div class="card" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0;">Sanctum Token Manager</h2>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0.25rem 0 0 0;">
                    Authenticate against <code>POST /api/login</code> to acquire a Bearer token required for write operations.
                </p>
            </div>
            <button id="quickFillBtn" type="button" class="btn" style="background: #e0e7ff; color: #4338ca; font-size: 0.8rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 6px; border: none; cursor: pointer;">
                Auto-Fill Sample Credentials
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: flex-end; margin-bottom: 1rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">User Email</label>
                <input type="email" id="loginEmail" placeholder="e.g. test@example.com" class="form-input" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem;" value="{{ Auth::check() ? Auth::user()->email : 'test@example.com' }}">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Password</label>
                <input type="password" id="loginPassword" placeholder="password" class="form-input" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem;" value="password">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" id="loginBtn" class="btn" style="background: var(--primary); color: #ffffff; font-weight: 600; padding: 0.55rem 1.25rem; border-radius: 6px; border: none; cursor: pointer;">
                    Obtain Token
                </button>
                <button type="button" id="logoutBtn" class="btn" style="background: #f1f5f9; color: #475569; font-weight: 600; padding: 0.55rem 1rem; border-radius: 6px; border: 1px solid var(--border); cursor: pointer;" disabled>
                    Revoke
                </button>
            </div>
        </div>

        <div id="tokenDisplayCard" style="display: none; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 0.75rem 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase;">Active Bearer Token:</span>
                <button type="button" id="copyTokenBtn" style="background: none; border: none; color: #4338ca; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                    Copy to Clipboard
                </button>
            </div>
            <code id="tokenString" style="display: block; word-break: break-all; font-size: 0.8rem; color: #0f172a; background: #e2e8f0; padding: 0.35rem 0.6rem; border-radius: 4px;"></code>
        </div>
    </div>

    {{-- Main Grid: Left Side Operations, Right Side Inspector --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">

        {{-- Left Column: Interactive API Operations --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

            {{-- Operation 1: GET /api/posts (Index) --}}
            <div class="card" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem; box-shadow: var(--shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div>
                        <span style="display: inline-block; padding: 0.15rem 0.5rem; background: #dcfce7; color: #166534; font-size: 0.75rem; font-weight: 700; border-radius: 4px; margin-right: 0.5rem;">GET</span>
                        <strong style="font-size: 0.95rem;">/api/posts</strong>
                    </div>
                    <button type="button" id="fetchPostsBtn" class="btn" style="background: #4f46e5; color: #ffffff; font-size: 0.85rem; font-weight: 600; padding: 0.4rem 0.85rem; border-radius: 6px; border: none; cursor: pointer;">
                        Fetch Posts (Collection)
                    </button>
                </div>
                <p style="font-size: 0.825rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                    Public collection endpoint transformed through <code>PostResource</code>. Includes comment counts and pagination.
                </p>

                {{-- Post List Preview Container --}}
                <div id="postsListLoading" style="display: none; padding: 1.5rem; text-align: center; color: var(--text-muted);">
                    <div style="display: inline-block; width: 24px; height: 24px; border: 3px solid #cbd5e1; border-top-color: #4f46e5; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                    <div style="font-size: 0.85rem; margin-top: 0.5rem;">Fetching posts from Laravel API...</div>
                </div>
                <div id="postsListContainer" style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 280px; overflow-y: auto;">
                    <div style="text-align: center; padding: 1.5rem; color: var(--text-muted); font-size: 0.85rem; border: 1px dashed var(--border); border-radius: 6px;">
                        Click <strong>Fetch Posts</strong> to load recent posts.
                    </div>
                </div>
            </div>

            {{-- Operation 2: GET /api/posts/{id} (Single & 404) --}}
            <div class="card" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem; box-shadow: var(--shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div>
                        <span style="display: inline-block; padding: 0.15rem 0.5rem; background: #dcfce7; color: #166534; font-size: 0.75rem; font-weight: 700; border-radius: 4px; margin-right: 0.5rem;">GET</span>
                        <strong style="font-size: 0.95rem;">/api/posts/{id}</strong>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Route Model Binding</span>
                </div>
                <p style="font-size: 0.825rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                    Query a specific post by ID. Automatically returns 404 JSON if the ID does not exist.
                </p>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="number" id="singlePostId" placeholder="Post ID (e.g. 1)" value="1" style="width: 120px; padding: 0.4rem 0.6rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
                    <button type="button" id="fetchSingleBtn" class="btn" style="background: #0284c7; color: #ffffff; font-size: 0.825rem; font-weight: 600; padding: 0.4rem 0.85rem; border-radius: 6px; border: none; cursor: pointer;">
                        Fetch Single
                    </button>
                    <button type="button" id="test404Btn" class="btn" style="background: #f1f5f9; color: #b91c1c; font-size: 0.825rem; font-weight: 600; padding: 0.4rem 0.75rem; border-radius: 6px; border: 1px solid #fca5a5; cursor: pointer;">
                        Test 404 (ID 99999)
                    </button>
                </div>
            </div>

            {{-- Operation 3: POST /api/posts (Store — Sanctum Protected) --}}
            <div class="card" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem; box-shadow: var(--shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div>
                        <span style="display: inline-block; padding: 0.15rem 0.5rem; background: #e0e7ff; color: #3730a3; font-size: 0.75rem; font-weight: 700; border-radius: 4px; margin-right: 0.5rem;">POST</span>
                        <strong style="font-size: 0.95rem;">/api/posts</strong>
                    </div>
                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: #4338ca; font-weight: 600;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        auth:sanctum
                    </span>
                </div>
                <p style="font-size: 0.825rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                    Creates a post. Validates title (min:3) and body (min:10). Generates slug automatically if omitted. Requires Bearer token.
                </p>

                <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.2rem;">Post Title *</label>
                        <input type="text" id="newPostTitle" placeholder="e.g. Exploring Modern Laravel 12 APIs" style="width: 100%; padding: 0.45rem 0.65rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.2rem;">Custom Slug (Optional)</label>
                        <input type="text" id="newPostSlug" placeholder="e.g. exploring-modern-laravel-apis" style="width: 100%; padding: 0.45rem 0.65rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.2rem;">Post Body *</label>
                        <textarea id="newPostBody" rows="3" placeholder="Write at least 10 characters of post content here..." style="width: 100%; padding: 0.45rem 0.65rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;"></textarea>
                    </div>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem;">
                        <button type="button" id="createPostBtn" class="btn" style="background: #16a34a; color: #ffffff; font-size: 0.85rem; font-weight: 600; padding: 0.5rem 1rem; border-radius: 6px; border: none; cursor: pointer; flex: 1;">
                            Submit Post (Status 201)
                        </button>
                        <button type="button" id="testValidationBtn" class="btn" style="background: #fef2f2; color: #b91c1c; font-size: 0.8rem; font-weight: 600; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fecaca; cursor: pointer;" title="Send empty payload to trigger 422">
                            Trigger 422
                        </button>
                    </div>
                </div>
            </div>

            {{-- Operation 4: PUT & DELETE Quick Actions (Sanctum Protected) --}}
            <div class="card" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem; box-shadow: var(--shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div>
                        <span style="display: inline-block; padding: 0.15rem 0.4rem; background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 700; border-radius: 4px; margin-right: 0.35rem;">PUT</span>
                        <span style="display: inline-block; padding: 0.15rem 0.4rem; background: #fee2e2; color: #991b1b; font-size: 0.75rem; font-weight: 700; border-radius: 4px; margin-right: 0.5rem;">DELETE</span>
                        <strong style="font-size: 0.95rem;">/api/posts/{id}</strong>
                    </div>
                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: #4338ca; font-weight: 600;">
                        auth:sanctum
                    </span>
                </div>
                <p style="font-size: 0.825rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                    Demonstrates resource update and deletion with Bearer token authorization.
                </p>
                <div style="display: grid; grid-template-columns: 100px 1fr 1fr; gap: 0.5rem;">
                    <input type="number" id="modPostId" placeholder="ID" value="1" style="padding: 0.4rem 0.5rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
                    <button type="button" id="updatePostBtn" class="btn" style="background: #d97706; color: #ffffff; font-size: 0.8rem; font-weight: 600; padding: 0.4rem 0.5rem; border-radius: 6px; border: none; cursor: pointer;">
                        Update Title (PUT 200)
                    </button>
                    <button type="button" id="deletePostBtn" class="btn" style="background: #dc2626; color: #ffffff; font-size: 0.8rem; font-weight: 600; padding: 0.4rem 0.5rem; border-radius: 6px; border: none; cursor: pointer;">
                        Delete Post (DEL 200)
                    </button>
                </div>
            </div>

        </div>

        {{-- Right Column: Live API Response Inspector --}}
        <div style="position: sticky; top: 5rem;">
            <div class="card" style="background: #0f172a; color: #f8fafc; border-radius: var(--radius); padding: 1.25rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); border: 1px solid #334155;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; border-bottom: 1px solid #1e293b; padding-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
                        <strong style="font-size: 0.95rem; color: #f1f5f9;">Live API Inspector</strong>
                    </div>
                    <button type="button" id="clearInspectorBtn" style="background: none; border: none; color: #94a3b8; font-size: 0.75rem; cursor: pointer;">
                        Clear
                    </button>
                </div>

                {{-- Status Bar --}}
                <div style="display: flex; justify-content: space-between; align-items: center; background: #1e293b; border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.75rem; font-size: 0.8rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span id="inspectorMethod" style="font-weight: 700; color: #a5b4fc;">READY</span>
                        <span id="inspectorUrl" style="color: #94a3b8; font-family: monospace; font-size: 0.75rem;">Awaiting request</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span id="inspectorTime" style="color: #64748b; font-size: 0.75rem;">-- ms</span>
                        <span id="inspectorStatus" style="font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; background: #334155; color: #94a3b8;">
                            ---
                        </span>
                    </div>
                </div>

                {{-- Headers Inspector --}}
                <div style="margin-bottom: 0.75rem;">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Request Headers</div>
                    <pre id="inspectorHeaders" style="margin: 0; background: #1e293b; padding: 0.5rem; border-radius: 4px; font-size: 0.75rem; color: #cbd5e1; font-family: monospace; max-height: 80px; overflow-y: auto;">Accept: application/json</pre>
                </div>

                {{-- JSON Body Inspector --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Response JSON Body</span>
                        <span id="inspectorSize" style="font-size: 0.7rem; color: #64748b;">0 bytes</span>
                    </div>
                    <pre id="inspectorBody" style="margin: 0; background: #1e293b; padding: 0.75rem; border-radius: 6px; font-size: 0.75rem; color: #38bdf8; font-family: 'Fira Code', Consolas, monospace; max-height: 380px; overflow-y: auto; white-space: pre-wrap; word-break: break-all;">{\n  "status": "ready",\n  "message": "Interact with any API action on the left to see live responses."\n}</pre>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Vanilla JS API Consumer Logic --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    let sanctumToken = localStorage.getItem('cynaris_sanctum_token') || '';

    // UI Elements
    const authStatusBadge   = document.getElementById('authStatusBadge');
    const tokenDisplayCard  = document.getElementById('tokenDisplayCard');
    const tokenString       = document.getElementById('tokenString');
    const loginBtn          = document.getElementById('loginBtn');
    const logoutBtn         = document.getElementById('logoutBtn');
    const quickFillBtn      = document.getElementById('quickFillBtn');
    const copyTokenBtn      = document.getElementById('copyTokenBtn');

    const fetchPostsBtn     = document.getElementById('fetchPostsBtn');
    const postsListLoading  = document.getElementById('postsListLoading');
    const postsListContainer= document.getElementById('postsListContainer');

    const fetchSingleBtn    = document.getElementById('fetchSingleBtn');
    const test404Btn        = document.getElementById('test404Btn');
    const singlePostId      = document.getElementById('singlePostId');

    const createPostBtn     = document.getElementById('createPostBtn');
    const testValidationBtn = document.getElementById('testValidationBtn');
    const newPostTitle      = document.getElementById('newPostTitle');
    const newPostSlug       = document.getElementById('newPostSlug');
    const newPostBody       = document.getElementById('newPostBody');

    const modPostId         = document.getElementById('modPostId');
    const updatePostBtn     = document.getElementById('updatePostBtn');
    const deletePostBtn     = document.getElementById('deletePostBtn');

    const inspectorMethod   = document.getElementById('inspectorMethod');
    const inspectorUrl      = document.getElementById('inspectorUrl');
    const inspectorStatus   = document.getElementById('inspectorStatus');
    const inspectorTime     = document.getElementById('inspectorTime');
    const inspectorHeaders  = document.getElementById('inspectorHeaders');
    const inspectorBody     = document.getElementById('inspectorBody');
    const inspectorSize     = document.getElementById('inspectorSize');
    const clearInspectorBtn = document.getElementById('clearInspectorBtn');

    // Update Token UI state
    function updateAuthState() {
        if (sanctumToken) {
            authStatusBadge.innerHTML = `
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
                <span style="color: #86efac;">Authenticated</span>
            `;
            tokenDisplayCard.style.display = 'block';
            tokenString.textContent = sanctumToken;
            logoutBtn.disabled = false;
        } else {
            authStatusBadge.innerHTML = `
                <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                <span style="color: #fca5a5;">Unauthenticated</span>
            `;
            tokenDisplayCard.style.display = 'none';
            tokenString.textContent = '';
            logoutBtn.disabled = true;
        }
    }

    updateAuthState();

    // Helper: Inspect Request and Response
    function renderInspector(method, url, headers, status, duration, data) {
        inspectorMethod.textContent = method;
        inspectorUrl.textContent = url;
        inspectorTime.textContent = `${duration} ms`;

        // Status pill colors
        inspectorStatus.textContent = `${status}`;
        if (status >= 200 && status < 300) {
            inspectorStatus.style.background = '#15803d';
            inspectorStatus.style.color = '#ffffff';
        } else if (status === 401 || status === 403) {
            inspectorStatus.style.background = '#b91c1c';
            inspectorStatus.style.color = '#ffffff';
        } else if (status === 404) {
            inspectorStatus.style.background = '#d97706';
            inspectorStatus.style.color = '#ffffff';
        } else if (status === 422) {
            inspectorStatus.style.background = '#7c2d12';
            inspectorStatus.style.color = '#fed7aa';
        } else {
            inspectorStatus.style.background = '#475569';
            inspectorStatus.style.color = '#f1f5f9';
        }

        let headerStr = Object.entries(headers).map(([k, v]) => `${k}: ${v}`).join('\n');
        inspectorHeaders.textContent = headerStr;

        const jsonStr = JSON.stringify(data, null, 2);
        inspectorBody.textContent = jsonStr;
        inspectorSize.textContent = `${new Blob([jsonStr]).size} bytes`;
    }

    // Generic Fetch Wrapper
    async function apiRequest(endpoint, options = {}) {
        const url = endpoint.startsWith('http') ? endpoint : `${window.location.origin}${endpoint}`;
        const method = options.method || 'GET';

        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...(options.headers || {})
        };

        if (sanctumToken) {
            headers['Authorization'] = `Bearer ${sanctumToken}`;
        }

        const startTime = performance.now();

        try {
            const response = await fetch(url, {
                method,
                headers,
                body: options.body ? JSON.stringify(options.body) : undefined,
            });

            const duration = Math.round(performance.now() - startTime);
            let responseData;
            try {
                responseData = await response.json();
            } catch (err) {
                responseData = { statusText: response.statusText, status: response.status };
            }

            renderInspector(method, endpoint, headers, response.status, duration, responseData);
            return { ok: response.ok, status: response.status, data: responseData };
        } catch (error) {
            const duration = Math.round(performance.now() - startTime);
            const errObj = { error: error.message };
            renderInspector(method, endpoint, headers, 0, duration, errObj);
            return { ok: false, status: 0, data: errObj };
        }
    }

    // Quick Fill sample credentials
    quickFillBtn.addEventListener('click', () => {
        document.getElementById('loginEmail').value = 'test@example.com';
        document.getElementById('loginPassword').value = 'password';
    });

    // Copy Token
    copyTokenBtn.addEventListener('click', () => {
        navigator.clipboard.writeText(sanctumToken);
        copyTokenBtn.textContent = 'Copied!';
        setTimeout(() => copyTokenBtn.textContent = 'Copy to Clipboard', 2000);
    });

    // Clear Inspector
    clearInspectorBtn.addEventListener('click', () => {
        inspectorMethod.textContent = 'READY';
        inspectorUrl.textContent = 'Awaiting request';
        inspectorStatus.textContent = '---';
        inspectorStatus.style.background = '#334155';
        inspectorTime.textContent = '-- ms';
        inspectorHeaders.textContent = 'Accept: application/json';
        inspectorBody.textContent = '{\n  "cleared": true\n}';
        inspectorSize.textContent = '0 bytes';
    });

    // 1. Sanctum Login (POST /api/login)
    loginBtn.addEventListener('click', async () => {
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        if (!email || !password) {
            alert('Please provide both email and password.');
            return;
        }

        loginBtn.disabled = true;
        loginBtn.textContent = 'Authenticating...';

        const result = await apiRequest('/api/login', {
            method: 'POST',
            body: { email, password, device_name: 'Frontend Demo Browser' }
        });

        loginBtn.disabled = false;
        loginBtn.textContent = 'Obtain Token';

        if (result.ok && result.data.token) {
            sanctumToken = result.data.token;
            localStorage.setItem('cynaris_sanctum_token', sanctumToken);
            updateAuthState();
        } else {
            alert(result.data.message || 'Authentication failed.');
        }
    });

    // 2. Sanctum Logout (POST /api/logout)
    logoutBtn.addEventListener('click', async () => {
        logoutBtn.disabled = true;
        logoutBtn.textContent = 'Revoking...';

        await apiRequest('/api/logout', { method: 'POST' });

        sanctumToken = '';
        localStorage.removeItem('cynaris_sanctum_token');
        updateAuthState();
        logoutBtn.textContent = 'Revoke';
    });

    // 3. GET Collection (/api/posts)
    async function loadPosts() {
        postsListLoading.style.display = 'block';
        postsListContainer.innerHTML = '';

        const result = await apiRequest('/api/posts?per_page=5');
        postsListLoading.style.display = 'none';

        if (result.ok && result.data.data) {
            const posts = result.data.data;
            if (posts.length === 0) {
                postsListContainer.innerHTML = '<div style="text-align: center; padding: 1rem; color: #64748b;">No posts found.</div>';
                return;
            }

            postsListContainer.innerHTML = posts.map(post => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.825rem;">
                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-right: 0.5rem;">
                        <span style="font-weight: 700; color: #4338ca;">#${post.id}</span>
                        <strong style="color: #0f172a; margin-left: 0.25rem;">${post.title}</strong>
                        <span style="color: #64748b; font-size: 0.75rem; margin-left: 0.4rem;">(${post.comments_count ?? 0} comments)</span>
                    </div>
                    <div style="display: flex; gap: 0.35rem; flex-shrink: 0;">
                        <button onclick="window.fetchSinglePost(${post.id})" style="background: #e0f2fe; color: #0369a1; border: none; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">View</button>
                        <button onclick="window.setPostToModify(${post.id})" style="background: #fef3c7; color: #92400e; border: none; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">Edit</button>
                    </div>
                </div>
            `).join('');
        }
    }

    fetchPostsBtn.addEventListener('click', loadPosts);

    // Global helper for card clicks
    window.fetchSinglePost = (id) => {
        singlePostId.value = id;
        fetchSingleBtn.click();
    };

    window.setPostToModify = (id) => {
        modPostId.value = id;
    };

    // 4. GET Single Post (/api/posts/{id})
    fetchSingleBtn.addEventListener('click', async () => {
        const id = singlePostId.value.trim();
        if (!id) return;
        await apiRequest(`/api/posts/${id}`);
    });

    test404Btn.addEventListener('click', async () => {
        singlePostId.value = 99999;
        await apiRequest('/api/posts/99999');
    });

    // 5. POST Create Post (/api/posts)
    createPostBtn.addEventListener('click', async () => {
        const title = newPostTitle.value.trim();
        const slug  = newPostSlug.value.trim();
        const body  = newPostBody.value.trim();

        createPostBtn.disabled = true;
        createPostBtn.textContent = 'Submitting...';

        const payload = { title, body };
        if (slug) payload.slug = slug;

        const result = await apiRequest('/api/posts', {
            method: 'POST',
            body: payload,
        });

        createPostBtn.disabled = false;
        createPostBtn.textContent = 'Submit Post (Status 201)';

        if (result.status === 201) {
            newPostTitle.value = '';
            newPostSlug.value = '';
            newPostBody.value = '';
            loadPosts();
        }
    });

    // Trigger 422 Validation Error
    testValidationBtn.addEventListener('click', async () => {
        await apiRequest('/api/posts', {
            method: 'POST',
            body: { title: '', body: '' },
        });
    });

    // 6. PUT Update Post (/api/posts/{id})
    updatePostBtn.addEventListener('click', async () => {
        const id = modPostId.value.trim();
        if (!id) return;

        updatePostBtn.disabled = true;
        updatePostBtn.textContent = 'Updating...';

        const updatedTitle = `Updated via API at ${new Date().toLocaleTimeString()}`;
        const result = await apiRequest(`/api/posts/${id}`, {
            method: 'PUT',
            body: {
                title: updatedTitle,
                body: 'Updated post body content through the RESTful API PUT endpoint.'
            }
        });

        updatePostBtn.disabled = false;
        updatePostBtn.textContent = 'Update Title (PUT 200)';

        if (result.ok) {
            loadPosts();
        }
    });

    // 7. DELETE Post (/api/posts/{id})
    deletePostBtn.addEventListener('click', async () => {
        const id = modPostId.value.trim();
        if (!id) return;

        if (!confirm(`Are you sure you want to delete post #${id} via the API?`)) return;

        deletePostBtn.disabled = true;
        deletePostBtn.textContent = 'Deleting...';

        const result = await apiRequest(`/api/posts/${id}`, {
            method: 'DELETE',
        });

        deletePostBtn.disabled = false;
        deletePostBtn.textContent = 'Delete Post (DEL 200)';

        if (result.ok) {
            loadPosts();
        }
    });
});
</script>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
@endsection
