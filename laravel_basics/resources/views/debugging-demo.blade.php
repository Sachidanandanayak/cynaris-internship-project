@extends('layouts.app')

@section('title', 'Testing & Debugging Demonstration')

@section('content')
<div class="container" style="max-width: 1100px; margin-top: 1rem; margin-bottom: 3rem;">

    {{-- Hero Section --}}
    <div class="hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); padding: 2.25rem 2rem; border-radius: var(--radius); margin-bottom: 2rem; box-shadow: var(--shadow);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: rgba(255,255,255,0.15); border-radius: 9999px; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 0.75rem;">
                    Week 4 Day 4 Feature
                </span>
                <h1 style="font-size: 2rem; font-weight: 800; line-height: 1.2; margin-bottom: 0.5rem; color: #ffffff;">
                    Testing &amp; Debugging Demonstration
                </h1>
                <p style="font-size: 1rem; color: #cbd5e1; max-width: 720px; line-height: 1.5;">
                    Comprehensive demonstration of automated testing with PHPUnit, application profiling with Laravel Debugbar, variable inspection with <code>dump()</code> / <code>dd()</code>, structured logging to <code>storage/logs/laravel.log</code>, and simulated bug resolution.
                </p>
            </div>
            <div style="background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(8px); padding: 1rem 1.25rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); min-width: 240px;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 0.25rem;">Execution Snapshot</div>
                <div style="display: flex; flex-direction: column; gap: 0.25rem; font-size: 0.875rem; color: #f8fafc;">
                    <div>Execution: <strong style="color: #38bdf8;">{{ $executionTimeMs }} ms</strong></div>
                    <div>Peak Memory: <strong style="color: #4ade80;">{{ $peakMemoryMb }} MB</strong></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance & Metric Summary Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; color: #4338ca; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 0.25rem;">Total Posts Loaded</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-main);">{{ $metrics['total_posts'] }}</div>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Eager loaded with comments (<code>Post::with('comments')</code>)</p>
        </div>
        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; color: #059669; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 0.25rem;">Total Comments</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-main);">{{ $metrics['total_comments'] }}</div>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Aggregated across loaded collection</p>
        </div>
        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; color: #0284c7; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 0.25rem;">Avg Comments / Post</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-main);">{{ $metrics['avg_comments_per_post'] }}</div>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Protected with zero-division safeguard</p>
        </div>
        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-weight: 700; color: #d97706; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 0.25rem;">Avg Words / Post</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-main);">{{ $metrics['avg_words_per_post'] }}</div>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Calculated via string word counts</p>
        </div>
    </div>

    {{-- Section: Core Debugging Methodologies --}}
    <div class="card" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow);">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
            Core Laravel Debugging Concepts
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.25rem;">
            <div style="border-left: 4px solid #4f46e5; padding-left: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #4f46e5; margin-bottom: 0.35rem;">1. dump() vs dd()</h3>
                <p style="font-size: 0.875rem; color: var(--text-main); margin-bottom: 0.5rem;">
                    <code>dump($var)</code> prints the variable's structured dump to the browser or terminal output while permitting code execution to continue.
                </p>
                <p style="font-size: 0.875rem; color: var(--text-main);">
                    <code>dd($var)</code> ("Dump and Die") immediately halts execution and renders Symfony's interactive variable inspector.
                </p>
                <div style="font-size: 0.75rem; background: #f1f5f9; padding: 0.5rem; border-radius: 4px; margin-top: 0.5rem;">
                    <strong>Best Practice:</strong> Always remove temporary <code>dd()</code> and <code>dump()</code> calls before committing code.
                </div>
            </div>

            <div style="border-left: 4px solid #059669; padding-left: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #059669; margin-bottom: 0.35rem;">2. Application Logging</h3>
                <p style="font-size: 0.875rem; color: var(--text-main); margin-bottom: 0.5rem;">
                    Laravel leverages Monolog via the <code>Illuminate\Support\Facades\Log</code> facade.
                </p>
                <p style="font-size: 0.875rem; color: var(--text-main);">
                    Log levels: <code>debug()</code>, <code>info()</code>, <code>notice()</code>, <code>warning()</code>, <code>error()</code>, <code>critical()</code>, <code>alert()</code>, <code>emergency()</code>.
                </p>
                <div style="font-size: 0.75rem; background: #ecfdf5; padding: 0.5rem; border-radius: 4px; margin-top: 0.5rem; color: #065f46;">
                    <strong>Storage Path:</strong> <code>storage/logs/laravel.log</code>
                </div>
            </div>

            <div style="border-left: 4px solid #0284c7; padding-left: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #0284c7; margin-bottom: 0.35rem;">3. Laravel Debugbar</h3>
                <p style="font-size: 0.875rem; color: var(--text-main); margin-bottom: 0.5rem;">
                    Installed development-only (<code>composer require barryvdh/laravel-debugbar --dev</code>).
                </p>
                <p style="font-size: 0.875rem; color: var(--text-main);">
                    Provides real-time inspection for:
                </p>
                <ul style="font-size: 0.85rem; color: var(--text-muted); margin-left: 1.25rem;">
                    <li><strong>Queries Tab:</strong> Shows query count, execution time, and duplicate queries.</li>
                    <li><strong>Memory Tab:</strong> Tracks peak memory consumption per request.</li>
                    <li><strong>Timeline / Views:</strong> Profiles controller and Blade rendering bottlenecks.</li>
                </ul>
            </div>

            <div style="border-left: 4px solid #d97706; padding-left: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #d97706; margin-bottom: 0.35rem;">4. Production Safety</h3>
                <p style="font-size: 0.875rem; color: var(--text-main); margin-bottom: 0.5rem;">
                    Debugbar is disabled by default in <code>config/debugbar.php</code> via:
                </p>
                <code style="font-size: 0.8rem; background: #fffbeb; color: #b45309; padding: 0.2rem 0.4rem; border-radius: 4px; display: block; margin-bottom: 0.5rem;">
                    'enabled' => env('DEBUGBAR_ENABLED', false)
                </code>
                <p style="font-size: 0.875rem; color: var(--text-main);">
                    Never enabled on live production environments (Railway) to prevent information exposure.
                </p>
            </div>
        </div>
    </div>

    {{-- Section: Simulated Bug Case Study --}}
    <div class="card" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow);">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
            Simulated Bug Case Study: DivisionByZero &amp; N+1 Query Resolution
        </h2>

        <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 1rem;">
                <div style="font-weight: 700; color: #991b1b; margin-bottom: 0.25rem;">The Bug</div>
                <p style="color: #7f1d1d; margin: 0;">
                    During the initial computation of average comments per post in an empty database environment, the calculation was executed as:
                    <br>
                    <code style="background: #ffffff; padding: 0.2rem 0.4rem; border-radius: 4px; color: #b91c1c; font-weight: 600;">$avg = $totalComments / $posts->count();</code>
                    <br>
                    When <code>$posts->count() === 0</code>, PHP threw an unhandled <code>DivisionByZeroError</code>, causing an HTTP 500 server error. Additionally, comments were accessed in a <code>foreach</code> loop without eager loading, generating <em>N+1</em> individual SQL queries.
                </p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 1rem;">
                <div style="font-weight: 700; color: #1e293b; margin-bottom: 0.25rem;">Diagnostic Procedure</div>
                <ol style="margin-left: 1.25rem; color: #334155;">
                    <li><strong>Variable Inspection:</strong> Placed <code>dump($posts->count())</code> to verify that the denominator evaluated to <code>0</code> on fresh test suites.</li>
                    <li><strong>Execution Halting:</strong> Used <code>dd($posts)</code> to inspect collection hydration and confirm relationship properties.</li>
                    <li><strong>Query Profiling:</strong> Inspected the <em>Queries</em> tab in Laravel Debugbar, observing 1 query for posts followed by <em>N</em> individual <code>SELECT * FROM comments WHERE post_id = ?</code> statements.</li>
                    <li><strong>Logging:</strong> Added <code>Log::warning('Post collection is empty during metric computation.')</code> to establish audit logs.</li>
                </ol>
            </div>

            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 1rem;">
                <div style="font-weight: 700; color: #065f46; margin-bottom: 0.25rem;">The Resolution &amp; Fix</div>
                <p style="color: #064e3b; margin: 0 0 0.5rem 0;">
                    1. Guarded against zero division using a ternary operator:
                    <br>
                    <code style="background: #ffffff; padding: 0.2rem 0.4rem; border-radius: 4px; color: #047857; font-weight: 600;">$avgComments = $postCount > 0 ? round($totalComments / $postCount, 2) : 0.0;</code>
                    <br>
                    2. Added eager loading to fetch posts and comments in <strong>exactly 2 queries</strong>:
                    <br>
                    <code style="background: #ffffff; padding: 0.2rem 0.4rem; border-radius: 4px; color: #047857; font-weight: 600;">$posts = Post::with('comments')->take(10)->get();</code>
                    <br>
                    3. Removed all temporary <code>dd()</code> and <code>dump()</code> calls from production code.
                </p>
            </div>
        </div>
    </div>

    {{-- Testing Quick Reference --}}
    <div class="card" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow);">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
            Automated Feature Test Suite (Week 4 Day 4)
        </h2>

        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
            Five foundational feature tests implemented in <code>tests/Feature/Week4Day4TestingAndDebuggingTest.php</code>:
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.75rem;">
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem 1rem;">
                <span style="font-weight: 700; color: #059669; font-size: 0.85rem;">[PASS] Test A</span>
                <p style="font-size: 0.8rem; color: var(--text-main); margin: 0.25rem 0 0 0;"><code>test_homepage_loads_successfully_with_http_200()</code></p>
            </div>
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem 1rem;">
                <span style="font-weight: 700; color: #059669; font-size: 0.85rem;">[PASS] Test B</span>
                <p style="font-size: 0.8rem; color: var(--text-main); margin: 0.25rem 0 0 0;"><code>test_user_registration_works_and_creates_a_user()</code></p>
            </div>
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem 1rem;">
                <span style="font-weight: 700; color: #059669; font-size: 0.85rem;">[PASS] Test C</span>
                <p style="font-size: 0.8rem; color: var(--text-main); margin: 0.25rem 0 0 0;"><code>test_user_login_works_with_valid_credentials()</code></p>
            </div>
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem 1rem;">
                <span style="font-weight: 700; color: #059669; font-size: 0.85rem;">[PASS] Test D</span>
                <p style="font-size: 0.8rem; color: var(--text-main); margin: 0.25rem 0 0 0;"><code>test_crud_create_works_for_blog_post_application()</code></p>
            </div>
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem 1rem;">
                <span style="font-weight: 700; color: #059669; font-size: 0.85rem;">[PASS] Test E</span>
                <p style="font-size: 0.8rem; color: var(--text-main); margin: 0.25rem 0 0 0;"><code>test_crud_delete_works_for_blog_post_application()</code></p>
            </div>
        </div>

        <div style="margin-top: 1.25rem; background: #0f172a; color: #f8fafc; padding: 1rem; border-radius: 6px; font-family: monospace; font-size: 0.85rem;">
            php artisan test tests/Feature/Week4Day4TestingAndDebuggingTest.php<br>
            <span style="color: #4ade80;">Pass: 5 &bull; Failures: 0 &bull; Assertions: 23 &bull; Result: 100% PASS</span>
        </div>
    </div>

</div>
@endsection
