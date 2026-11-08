<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CiaSearchLog;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\Vector\CiaMemoryLayerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CiaMemoryLayerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Unauthenticated requests to memory layer endpoints return 401.
     */
    public function test_unauthenticated_user_cannot_access_memory_layer(): void
    {
        $this->getJson('/candidate/cia/career/memory/match')->assertStatus(401);
        $this->postJson('/candidate/cia/career/memory/seed', [])->assertStatus(401);
    }

    /**
     * Test 2: Seeding endpoint synchronizes all 20 technical role profiles.
     */
    public function test_memory_layer_seeding_synchronizes_twenty_roles(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/memory/seed', []);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '20 role profiles successfully synchronized to memory layer.',
            'data' => [
                'seeded_count' => 20,
            ],
        ]);

        $this->assertCount(20, $response->json('data.roles'));
    }

    /**
     * Test 3: Vector search returns top-5 matched roles with required fields.
     */
    public function test_memory_search_returns_top_five_ranked_matches(): void
    {
        $user = User::factory()->create();

        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Laravel MVC & Eloquent',
            'skill' => 'PHP',
            'is_completed' => true,
            'score' => 90.0,
        ]);

        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Building REST APIs',
            'skill' => 'Laravel',
            'is_completed' => true,
            'score' => 88.0,
        ]);

        QuizScore::create([
            'user_id' => $user->id,
            'quiz_title' => 'Database Schemas',
            'skill' => 'MySQL',
            'score' => 18,
            'max_score' => 20,
            'percentage' => 90.0,
        ]);

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/memory/match');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'meta' => [
                'candidate_id',
                'total_matched',
                'limit',
                'engine',
                'collection',
            ],
            'data' => [
                '*' => [
                    'role_id',
                    'role',
                    'domain',
                    'experience_level',
                    'affinity_percentage',
                    'vector_distance',
                    'matching_skills',
                    'missing_skills',
                    'match_criteria',
                    'required_skills',
                ],
            ],
        ]);

        $matches = $response->json('data');
        $this->assertCount(5, $matches);

        // Verify ranked descending
        $this->assertGreaterThanOrEqual($matches[1]['affinity_percentage'], $matches[0]['affinity_percentage']);
        $this->assertGreaterThanOrEqual($matches[2]['affinity_percentage'], $matches[1]['affinity_percentage']);
    }

    /**
     * Test 4: Affinity percentage formula is verified:
     * Affinity % = max(0, min(100, round((1.0 - distance) * 100, 1)))
     */
    public function test_affinity_percentage_and_distance_relationship(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/memory/match');
        $response->assertStatus(200);

        $matches = $response->json('data');
        foreach ($matches as $match) {
            $dist = $match['vector_distance'];
            $expectedAffinity = max(0.0, min(100.0, round((1.0 - $dist) * 100.0, 1)));
            $this->assertEqualsWithDelta($expectedAffinity, $match['affinity_percentage'], 0.15);
            $this->assertGreaterThanOrEqual(0.0, $match['affinity_percentage']);
            $this->assertLessThanOrEqual(100.0, $match['affinity_percentage']);
        }
    }

    /**
     * Test 5: Skill overlap partitions matching competencies and missing gaps accurately.
     */
    public function test_skill_overlap_identifies_matching_and_missing_competencies(): void
    {
        $service = new CiaMemoryLayerService();
        $skillProfile = [
            ['skill' => 'PHP', 'proficiency_score' => 90.0],
            ['skill' => 'Laravel', 'proficiency_score' => 85.0],
            ['skill' => 'MySQL', 'proficiency_score' => 80.0],
        ];

        $results = $service->deterministicRoleSearch($skillProfile, 5);

        $this->assertCount(5, $results);

        // Junior Backend Laravel Engineer requires: PHP, Laravel, MySQL, REST APIs, Git, MVC Architecture
        $juniorBackend = collect($results)->firstWhere('role', 'Junior Backend Laravel Engineer');
        $this->assertNotNull($juniorBackend);
        $this->assertContains('PHP', $juniorBackend['matching_skills']);
        $this->assertContains('Laravel', $juniorBackend['matching_skills']);
        $this->assertContains('MySQL', $juniorBackend['matching_skills']);
        $this->assertContains('REST APIs', $juniorBackend['missing_skills']);
        $this->assertContains('Git', $juniorBackend['missing_skills']);
    }

    /**
     * Test 6: Memory role search persists audit telemetry log into cia_search_logs.
     */
    public function test_memory_search_persists_cia_search_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/candidate/cia/career/memory/match');

        $this->assertDatabaseHas('cia_search_logs', [
            'user_id' => $user->id,
            'query_type' => 'memory_role_match',
            'status' => 'completed',
        ]);

        $log = CiaSearchLog::where('user_id', $user->id)->where('query_type', 'memory_role_match')->first();
        $this->assertNotEmpty($log->target_role);
        $this->assertGreaterThan(0, $log->readiness_score);
    }

    /**
     * Test 7: Memory layer handles candidates with zero coursework gracefully.
     */
    public function test_memory_layer_handles_empty_candidate_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/memory/match');

        $response->assertStatus(200);
        $matches = $response->json('data');
        $this->assertCount(5, $matches);
        $this->assertNotEmpty($matches[0]['role']);
    }

    /**
     * Test 8: Deterministic fallback executes safely when Python invocation fails.
     */
    public function test_deterministic_fallback_when_python_engine_returns_null(): void
    {
        $mockService = $this->getMockBuilder(CiaMemoryLayerService::class)
            ->onlyMethods(['invokePythonEngine'])
            ->getMock();

        $mockService->method('invokePythonEngine')->willReturn(null);

        $user = User::factory()->create();

        $result = $mockService->searchRoleMatches($user, ['limit' => 5]);

        $this->assertTrue($result['success']);
        $this->assertEquals('chromadb_fallback_engine', $result['meta']['engine']);
        $this->assertCount(5, $result['data']);
    }
}
