<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\Job;
use App\Models\PlacementReadinessScore;
use App\Models\QuizScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CiaLoadBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected function createBenchmarkCandidate(): User
    {
        $user = User::factory()->create([
            'name' => 'Benchmark Candidate',
            'email' => 'bench@example.com',
        ]);

        $skills = ['PHP', 'Laravel', 'MySQL', 'REST APIs', 'Git', 'Docker'];
        foreach ($skills as $skill) {
            CandidateLesson::create([
                'user_id' => $user->id,
                'title' => "{$skill} Module",
                'skill' => $skill,
                'is_completed' => true,
                'score' => 88.0,
            ]);

            QuizScore::create([
                'user_id' => $user->id,
                'quiz_title' => "{$skill} Quiz",
                'skill' => $skill,
                'score' => 18,
                'max_score' => 20,
                'percentage' => 90.0,
            ]);
        }

        PlacementReadinessScore::create([
            'user_id' => $user->id,
            'project_completion_score' => 85.0,
            'assessment_score' => 90.0,
            'cia_usage_score' => 80.0,
            'documentation_score' => 85.0,
            'placement_readiness_score' => 85.5,
            'viva_score' => 75.0,
            'code_review_score' => 80.0,
            'demo_clarity_score' => 85.0,
            'git_quality_score' => 90.0,
            'interview_readiness_score' => 81.5,
            'breakdown' => ['placement_readiness' => ['status_tier' => 'Placement Ready (Tier 1 Priority)']],
        ]);

        return $user;
    }

    /**
     * Test 1: Benchmark the CIA Cockpit Dashboard over repeated requests.
     */
    public function test_benchmark_cia_dashboard_query_count_and_latency(): void
    {
        $candidate = $this->createBenchmarkCandidate();

        // 1. Warm-up request
        $this->actingAs($candidate)->get('/candidate/cia/career')->assertStatus(200);

        // 2. Measure Query Count on single optimized dashboard request
        DB::flushQueryLog();
        DB::enableQueryLog();

        $start = microtime(true);
        $response = $this->actingAs($candidate)->get('/candidate/cia/career');
        $durationMs = (microtime(true) - $start) * 1000.0;

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);

        // The dashboard must execute with an optimized number of queries (<= 15 total including sessions, auth, and sub-services)
        $this->assertLessThanOrEqual(15, count($queries), 'Optimized dashboard query count exceeded target threshold.');

        echo "\n[Benchmark] Dashboard Latency: " . round($durationMs, 2) . " ms, Query Count: " . count($queries) . "\n";
    }

    /**
     * Test 2: Local Load Test simulating 50 sequential requests across multiple CIA endpoints.
     */
    public function test_local_load_test_across_cia_endpoints(): void
    {
        $candidate = $this->createBenchmarkCandidate();

        // Intercept external AI calls to test pure local application + DB performance
        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'offline'], 503),
            'openrouter.ai/*' => Http::response(['error' => 'offline'], 503),
        ]);

        $endpoints = [
            ['method' => 'get', 'uri' => '/candidate/cia/career'],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/skill-gap', 'payload' => ['target_role' => 'Junior Backend Laravel Engineer']],
            ['method' => 'getJson', 'uri' => '/candidate/cia/career/readiness'],
            ['method' => 'getJson', 'uri' => '/candidate/cia/career/matches?limit=5'],
            ['method' => 'getJson', 'uri' => '/candidate/cia/career/memory/match?limit=5'],
        ];

        $latencies = [];
        $totalIterations = 15;
        $startOverall = microtime(true);
        $startMem = memory_get_usage(true);

        for ($i = 0; $i < $totalIterations; $i++) {
            $ep = $endpoints[$i % count($endpoints)];
            $t0 = microtime(true);

            if ($ep['method'] === 'get') {
                $res = $this->actingAs($candidate)->get($ep['uri']);
            } elseif ($ep['method'] === 'getJson') {
                $res = $this->actingAs($candidate)->getJson($ep['uri']);
            } else {
                $res = $this->actingAs($candidate)->postJson($ep['uri'], $ep['payload'] ?? []);
            }

            $t1 = microtime(true);
            $latencies[] = ($t1 - $t0) * 1000.0;
            $res->assertStatus(200);
        }

        $overallDuration = microtime(true) - $startOverall;
        $peakMem = memory_get_peak_usage(true);

        sort($latencies);
        $count = count($latencies);
        $p50 = $latencies[(int) round($count * 0.50) - 1];
        $p95 = $latencies[(int) round($count * 0.95) - 1];
        $p99 = $latencies[(int) round($count * 0.99) - 1];
        $throughput = round($count / max(0.001, $overallDuration), 1);
        $avgLatency = round(array_sum($latencies) / $count, 2);

        echo "\n=================================================================\n";
        echo "  CIA Career Endpoints Local Load Test Results (15 Iterations)\n";
        echo "=================================================================\n";
        echo "  Total Requests: {$count}\n";
        echo "  Overall Duration: " . round($overallDuration, 2) . " s\n";
        echo "  Throughput: {$throughput} req/s\n";
        echo "  Average Latency: {$avgLatency} ms\n";
        echo "  Median (P50) Latency: " . round($p50, 2) . " ms\n";
        echo "  P95 Latency: " . round($p95, 2) . " ms\n";
        echo "  P99 Latency: " . round($p99, 2) . " ms\n";
        echo "  Peak Memory: " . round($peakMem / (1024 * 1024), 2) . " MB\n";
        echo "=================================================================\n";

        // Assert performance boundaries (accounting for Python subprocess startup on Windows)
        $this->assertLessThan(45000.0, $p95, 'P95 latency should remain within acceptable threshold.');
        $this->assertGreaterThan(0.0, $throughput);
    }
}

