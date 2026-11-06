<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CiaSearchLog;
use App\Models\Job;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\Vector\ChromaDbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CiaChromaDbMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed sample internship jobs for matching
        $recruiter = User::factory()->create([
            'email' => 'talent@cynaris.local',
        ]);

        Job::create([
            'user_id' => $recruiter->id,
            'title' => 'Graduate Software Engineer — Laravel Intern',
            'company' => 'Cynaris Systems',
            'location' => 'Pune, India (On-site)',
            'employment_type' => 'Internship',
            'description' => 'Contribute to core Laravel services and PHPUnit test suites.',
            'requirements' => 'Required competencies: PHP, Laravel, MySQL, Git, and REST APIs.',
            'salary_range' => '₹30,000 - ₹45,000 / mo',
            'application_deadline' => now()->addDays(30),
            'status' => 'active',
        ]);

        Job::create([
            'user_id' => $recruiter->id,
            'title' => 'Cloud Infrastructure & DevOps Intern',
            'company' => 'Apex Cloud Solutions',
            'location' => 'Hyderabad, India (Hybrid)',
            'employment_type' => 'Internship',
            'description' => 'Container orchestration and automated CI/CD pipelines.',
            'requirements' => 'Required competencies: Docker, Kubernetes, Linux, AWS, and Git.',
            'salary_range' => '₹35,000 - ₹48,000 / mo',
            'application_deadline' => now()->addDays(28),
            'status' => 'active',
        ]);

        Job::create([
            'user_id' => $recruiter->id,
            'title' => 'Frontend UI/UX & Tailwind CSS Intern',
            'company' => 'Vanguard Studios',
            'location' => 'Remote (Global)',
            'employment_type' => 'Internship',
            'description' => 'Responsive user interfaces and Tailwind CSS components.',
            'requirements' => 'Required competencies: JavaScript, Vue.js, HTML5, CSS3, and Tailwind CSS.',
            'salary_range' => '₹28,000 - ₹40,000 / mo',
            'application_deadline' => now()->addDays(40),
            'status' => 'active',
        ]);

        Job::create([
            'user_id' => $recruiter->id,
            'title' => 'AI & LLM Integration Engineering Intern',
            'company' => 'Cynaris AI Research',
            'location' => 'Bengaluru, India (Hybrid)',
            'employment_type' => 'Internship',
            'description' => 'Agentic workflows and ChromaDB similarity search.',
            'requirements' => 'Required competencies: Python, PHP, ChromaDB, Groq AI, and LlamaIndex.',
            'salary_range' => '₹40,000 - ₹55,000 / mo',
            'application_deadline' => now()->addDays(20),
            'status' => 'active',
        ]);

        Job::create([
            'user_id' => $recruiter->id,
            'title' => 'Backend API & Microservices Intern',
            'company' => 'CloudScale Labs',
            'location' => 'Remote (India)',
            'employment_type' => 'Internship',
            'description' => 'Build high-throughput REST APIs and Redis caching layers.',
            'requirements' => 'Required competencies: PHP, Laravel, REST APIs, Redis, and MySQL.',
            'salary_range' => '₹35,000 - ₹50,000 / mo',
            'application_deadline' => now()->addDays(25),
            'status' => 'active',
        ]);

        Job::create([
            'user_id' => $recruiter->id,
            'title' => 'Database Optimization & SQL Intern',
            'company' => 'HyperScale Data',
            'location' => 'Mumbai, India (On-site)',
            'employment_type' => 'Internship',
            'description' => 'Schema profiling and relational query optimization.',
            'requirements' => 'Required competencies: MySQL, PostgreSQL, Indexing, and Eloquent ORM.',
            'salary_range' => '₹32,000 - ₹44,000 / mo',
            'application_deadline' => now()->addDays(30),
            'status' => 'active',
        ]);
    }

    /**
     * Test 1: Unauthenticated request to sync profile is rejected with 401.
     */
    public function test_unauthenticated_user_cannot_sync_vector_profile(): void
    {
        $response = $this->postJson('/candidate/cia/career/profile/sync');
        $response->assertStatus(401);
    }

    /**
     * Test 2: Unauthenticated request to matches is rejected with 401.
     */
    public function test_unauthenticated_user_cannot_access_semantic_matches(): void
    {
        $response = $this->getJson('/candidate/cia/career/matches');
        $response->assertStatus(401);
    }

    /**
     * Test 3: Candidate profile sync returns 202 with pseudonymous ID and zero PII.
     */
    public function test_candidate_profile_sync_succeeds_with_pseudonymous_id(): void
    {
        $user = User::factory()->create([
            'name' => 'Alice Private Candidate',
            'email' => 'alice.private@example.com',
            'phone' => '+15550001111',
        ]);

        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Laravel MVC Architecture',
            'skill' => 'Laravel',
            'is_completed' => true,
            'score' => 95.0,
            'completed_at' => now(),
        ]);

        QuizScore::create([
            'user_id' => $user->id,
            'quiz_title' => 'Laravel Mastery',
            'skill' => 'Laravel',
            'score' => 48.0,
            'max_score' => 50.0,
            'percentage' => 96.0,
            'passed' => true,
            'attempted_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/profile/sync', [
            'force_refresh' => true,
            'sync_source' => 'user_manual_trigger',
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'sync_job_id',
                    'candidate_id',
                    'status',
                    'target_collection',
                    'last_synced_at',
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('cand_' . $user->id, $response->json('data.candidate_id'));
        $this->assertEquals('cynaris_intern_profiles', $response->json('data.target_collection'));
        $this->assertEquals('SYNCHRONIZED', $response->json('data.status'));

        // Zero-PII verification: ensure candidate name, email, or phone are never exposed in sync response
        $rawContent = $response->getContent();
        $this->assertStringNotContainsString('Alice Private Candidate', $rawContent);
        $this->assertStringNotContainsString('alice.private@example.com', $rawContent);
        $this->assertStringNotContainsString('+15550001111', $rawContent);
    }

    /**
     * Test 4: Semantic matching returns top-5 matches with affinity percentage and vector distance.
     */
    public function test_semantic_matching_returns_top_matches_with_affinity_and_distance(): void
    {
        $user = User::factory()->create();

        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'PHP 8 Basics & Functions',
            'skill' => 'PHP',
            'is_completed' => true,
            'score' => 90.0,
            'completed_at' => now(),
        ]);

        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Laravel Eloquent ORM',
            'skill' => 'Laravel',
            'is_completed' => true,
            'score' => 92.0,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/matches?limit=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'meta' => [
                    'candidate_id',
                    'total_matched',
                    'query_parameters' => [
                        'domain',
                        'min_affinity',
                        'location_type',
                        'limit',
                    ],
                    'engine',
                ],
                'data' => [
                    '*' => [
                        'job_id',
                        'title',
                        'company',
                        'location',
                        'location_type',
                        'domain',
                        'affinity_percentage',
                        'vector_distance',
                        'matching_skills',
                        'missing_skills',
                    ],
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $matches = $response->json('data');
        $this->assertLessThanOrEqual(5, count($matches));

        foreach ($matches as $match) {
            $this->assertIsFloat((float) $match['affinity_percentage']);
            $this->assertGreaterThanOrEqual(0.0, (float) $match['affinity_percentage']);
            $this->assertLessThanOrEqual(100.0, (float) $match['affinity_percentage']);
            $this->assertIsFloat((float) $match['vector_distance']);
        }
    }

    /**
     * Test 5: Semantic matching respects domain filter.
     */
    public function test_semantic_matching_filters_by_domain(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/matches?domain=frontend');

        $response->assertStatus(200);
        $matches = $response->json('data');

        foreach ($matches as $match) {
            $this->assertEquals('Frontend', $match['domain']);
        }
    }

    /**
     * Test 6: Semantic matching respects location_type filter.
     */
    public function test_semantic_matching_filters_by_location_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/matches?location_type=remote');

        $response->assertStatus(200);
        $matches = $response->json('data');

        foreach ($matches as $match) {
            $this->assertEquals('Remote', $match['location_type']);
        }
    }

    /**
     * Test 7: Semantic matching respects min_affinity threshold.
     */
    public function test_semantic_matching_filters_by_min_affinity_threshold(): void
    {
        $user = User::factory()->create();

        $threshold = 60.0;
        $response = $this->actingAs($user)->getJson("/candidate/cia/career/matches?min_affinity={$threshold}");

        $response->assertStatus(200);
        $matches = $response->json('data');

        foreach ($matches as $match) {
            $this->assertGreaterThanOrEqual($threshold, (float) $match['affinity_percentage']);
        }
    }

    /**
     * Test 8: Semantic match queries persist to cia_search_logs table.
     */
    public function test_semantic_matching_persists_to_cia_search_logs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/matches?domain=backend');
        $response->assertStatus(200);

        $this->assertDatabaseHas('cia_search_logs', [
            'user_id' => $user->id,
            'query_type' => 'semantic_match',
            'status' => 'completed',
        ]);

        $log = CiaSearchLog::where('user_id', $user->id)->where('query_type', 'semantic_match')->first();
        $this->assertNotNull($log);
        $this->assertIsArray($log->recommendations);
        $this->assertEquals('cand_' . $user->id, $log->metadata['candidate_pseudonym'] ?? null);
    }

    /**
     * Test 9: Graceful fallback when ChromaDB engine throws or returns null.
     */
    public function test_semantic_matching_graceful_fallback_when_chromadb_fails(): void
    {
        $user = User::factory()->create();

        // Bind mock service that simulates ChromaDB execution failure
        $mock = $this->partialMock(ChromaDbService::class, function ($mock) {
            $mock->shouldReceive('invokePythonEngine')->andReturn(null);
        });

        $this->app->instance(ChromaDbService::class, $mock);

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/matches');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('chromadb_fallback', $response->json('meta.engine'));
        $this->assertNotEmpty($response->json('data'));
    }

    /**
     * Test 10: Web cockpit renders ChromaDB Matching section and sync button.
     */
    public function test_cia_career_tab_renders_internship_matching_section(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/candidate/cia/career');

        $response->assertStatus(200);
        $response->assertSee('Module 3: ChromaDB Engine');
        $response->assertSee('AI Semantic Internship Matching Engine');
        $response->assertSee('syncVectorBtn');
        $response->assertSee('matchingFilterForm');
        $response->assertSee('filterMatchesBtn');
    }
}

