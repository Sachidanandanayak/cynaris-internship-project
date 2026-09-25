<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Week 4 Day 4: Testing & Debugging Demonstration Controller
 *
 * Demonstrates:
 * 1. Variable inspection techniques using dump() and temporary dd() during development.
 * 2. Application logging with Monolog via Illuminate\Support\Facades\Log.
 * 3. Database query optimization and memory profiling with Laravel Debugbar.
 * 4. Safe reproduction, diagnosis, and fix of a simulated aggregation bug.
 */
class DebuggingDemoController extends Controller
{
    /**
     * Display the debugging demonstration dashboard.
     *
     * In this demonstration:
     * - Eager loading is utilized to prevent N+1 query overhead.
     * - Aggregations are calculated with zero-division guards.
     * - Diagnostics are logged to storage/logs/laravel.log without exposing secrets.
     */
    public function index(Request $request): View
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // 1. Logging initial invocation
        Log::info('Week 4 Day 4: DebuggingDemoController@index invoked', [
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route'      => $request->path(),
        ]);

        // 2. Fetch posts with comments eager loaded (resolves simulated N+1 query issue)
        $posts = Post::with('comments')->take(10)->get();

        // Debug logging of collection size
        Log::debug('Retrieved posts for metric computation', [
            'count' => $posts->count(),
        ]);

        // 3. Compute metrics with safe guards (resolving simulated zero-division bug)
        $metrics = $this->calculateMetrics($posts);

        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
        $peakMemoryMb = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        // 4. Log completion metrics
        Log::info('Week 4 Day 4: Debugging metrics computed successfully', [
            'total_posts'       => $metrics['total_posts'],
            'total_comments'    => $metrics['total_comments'],
            'average_comments'  => $metrics['avg_comments_per_post'],
            'execution_time_ms' => $executionTimeMs,
            'peak_memory_mb'    => $peakMemoryMb,
        ]);

        return view('debugging-demo', compact('posts', 'metrics', 'executionTimeMs', 'peakMemoryMb'));
    }

    /**
     * Calculate post & comment engagement metrics.
     *
     * SIMULATED BUG & FIX CONTEXT:
     * Bug:
     * In the original draft, the average comments were calculated as:
     *   $avg = $totalComments / $postCount;
     * When $postCount was 0 (empty database in fresh test/dev environments),
     * this threw an unhandled DivisionByZeroError.
     *
     * Debugging Steps:
     * 1. dump($posts->count()) confirmed $postCount evaluated to 0.
     * 2. dd($posts) inspected collection state and halted execution.
     * 3. Log::warning() recorded the zero-post scenario to storage/logs/laravel.log.
     *
     * Fix:
     * Guarded with ternary: $postCount > 0 ? round($totalComments / $postCount, 2) : 0.0.
     * Removed all temporary dd() calls before production.
     */
    public function calculateMetrics($posts): array
    {
        $postCount = $posts->count();
        $totalComments = 0;
        $totalWords = 0;

        foreach ($posts as $post) {
            $totalComments += $post->comments->count();
            $totalWords += str_word_count(strip_tags($post->body ?? ''));
        }

        if ($postCount === 0) {
            Log::warning('Week 4 Day 4: Post collection is empty during metric computation. Applied zero-division guard.');
        }

        $avgComments = $postCount > 0 ? round($totalComments / $postCount, 2) : 0.0;
        $avgWords = $postCount > 0 ? round($totalWords / $postCount, 2) : 0.0;

        return [
            'total_posts'            => $postCount,
            'total_comments'         => $totalComments,
            'total_words'            => $totalWords,
            'avg_comments_per_post'  => $avgComments,
            'avg_words_per_post'     => $avgWords,
        ];
    }
}
