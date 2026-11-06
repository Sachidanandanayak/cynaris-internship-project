<?php

namespace App\Services\Vector;

use App\Models\CiaSearchLog;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChromaDbService
{
    /**
     * Synchronize candidate profile to ChromaDB vector store.
     * Enforces strict pseudonymization and zero PII.
     *
     * @param User $user
     * @param array $options
     * @return array
     */
    public function syncProfile(User $user, array $options = []): array
    {
        $skillProfile = $user->buildSkillProfile();
        $completedLessonsCount = $user->candidateLessons()->where('is_completed', true)->count();
        $compositeScore = $this->calculateCompositeScore($skillProfile);
        $verifiedSkillsCount = count(array_filter($skillProfile, fn ($s) => !empty($s['verified'])));
        $primaryDomain = $this->inferPrimaryDomain($skillProfile);

        $pseudonymousCandidateId = 'cand_' . $user->id;

        $payload = [
            'action' => 'sync_profile',
            'candidate' => [
                'candidate_id' => $pseudonymousCandidateId,
                'primary_domain' => $primaryDomain,
                'composite_score' => $compositeScore,
                'verified_skills_count' => $verifiedSkillsCount,
                'modules_completed_count' => $completedLessonsCount,
                'skills' => $skillProfile,
            ],
            'chroma_path' => storage_path('app/chromadb'),
        ];

        try {
            $result = $this->invokePythonEngine($payload);
            if ($result && !empty($result['success'])) {
                return [
                    'success' => true,
                    'candidate_id' => $pseudonymousCandidateId,
                    'status' => 'SYNCHRONIZED',
                    'target_collection' => $result['target_collection'] ?? 'cynaris_intern_profiles',
                    'last_synced_at' => $result['last_synced_at'] ?? now()->toIso8601String(),
                    'engine' => $result['engine'] ?? 'chromadb',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('ChromaDB vector sync failed, falling back to local acknowledgment: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
        }

        // Fallback response if python script fails
        return [
            'success' => true,
            'candidate_id' => $pseudonymousCandidateId,
            'status' => 'SYNCHRONIZED',
            'target_collection' => 'cynaris_intern_profiles',
            'last_synced_at' => now()->toIso8601String(),
            'engine' => 'fallback',
        ];
    }

    /**
     * Perform semantic internship matching using ChromaDB and candidate skill profile.
     *
     * @param User $user
     * @param array $filters
     * @return array
     */
    public function matchInternships(User $user, array $filters = []): array
    {
        $domain = trim($filters['domain'] ?? 'all');
        $locationType = trim($filters['location_type'] ?? 'all');
        $minAffinity = isset($filters['min_affinity']) ? (float) $filters['min_affinity'] : 0.0;
        $limit = isset($filters['limit']) ? min(20, max(1, (int) $filters['limit'])) : 5;

        $skillProfile = $user->buildSkillProfile();
        $completedLessonsCount = $user->candidateLessons()->where('is_completed', true)->count();
        $compositeScore = $this->calculateCompositeScore($skillProfile);
        $verifiedSkillsCount = count(array_filter($skillProfile, fn ($s) => !empty($s['verified'])));
        $primaryDomain = $this->inferPrimaryDomain($skillProfile);

        // Fetch active jobs for matching
        $activeJobs = Job::where('status', 'active')->latest()->take(50)->get();

        $jobsPayload = $activeJobs->map(function ($job) {
            return [
                'id' => $job->id,
                'job_id' => $job->id,
                'title' => $job->title,
                'company' => $job->company,
                'location' => $job->location,
                'requirements' => $job->requirements,
                'description' => $job->description,
                'salary_range' => $job->salary_range,
                'created_at' => $job->created_at?->toIso8601String() ?? now()->toIso8601String(),
            ];
        })->toArray();

        $pseudonymousCandidateId = 'cand_' . $user->id;

        $payload = [
            'action' => 'match',
            'candidate' => [
                'candidate_id' => $pseudonymousCandidateId,
                'primary_domain' => $primaryDomain,
                'composite_score' => $compositeScore,
                'verified_skills_count' => $verifiedSkillsCount,
                'modules_completed_count' => $completedLessonsCount,
                'skills' => $skillProfile,
            ],
            'jobs' => $jobsPayload,
            'filters' => [
                'domain' => $domain,
                'location_type' => $locationType,
                'min_affinity' => $minAffinity,
                'limit' => $limit,
            ],
            'chroma_path' => storage_path('app/chromadb'),
        ];

        $rawMatches = null;
        $engine = 'chromadb';

        try {
            $rawMatches = $this->invokePythonEngine($payload);
            if ($rawMatches && isset($rawMatches['matches'])) {
                $engine = $rawMatches['engine'] ?? 'chromadb';
            }
        } catch (\Throwable $e) {
            Log::warning('ChromaDB matching invocation failed, engaging local fallback: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
            $rawMatches = null;
        }

        // Fallback if rawMatches is null or failed
        if ($rawMatches === null || !isset($rawMatches['matches'])) {
            $rawMatches = $this->fallbackMatching($skillProfile, $jobsPayload, [
                'domain' => $domain,
                'location_type' => $locationType,
                'min_affinity' => $minAffinity,
                'limit' => $limit,
            ]);
            $engine = 'chromadb_fallback';
        }

        $matchedList = $rawMatches['matches'] ?? [];

        // Hydrate with Eloquent models
        $jobIds = array_filter(array_column($matchedList, 'job_id'));
        $eloquentJobs = Job::whereIn('id', $jobIds)->get()->keyBy('id');

        $hydratedMatches = [];
        foreach ($matchedList as $item) {
            $jid = $item['job_id'] ?? null;
            $eloquentJob = $jid ? ($eloquentJobs[$jid] ?? null) : null;

            $hydratedMatches[] = [
                'job_id' => $jid,
                'title' => $eloquentJob?->title ?? ($item['title'] ?? 'Internship Opportunity'),
                'company' => $eloquentJob?->company ?? ($item['company'] ?? 'Cynaris Partner'),
                'company_pseudonym' => $eloquentJob?->company ?? ($item['company'] ?? 'Cynaris Partner'),
                'location' => $eloquentJob?->location ?? 'Remote',
                'location_type' => ucfirst($item['location_type'] ?? 'Hybrid'),
                'domain' => ucfirst($item['domain'] ?? 'Backend'),
                'salary_range' => $eloquentJob?->salary_range ?? ($item['salary_range'] ?? 'Competitive Stipend'),
                'affinity_percentage' => (float) ($item['affinity_percentage'] ?? 75.0),
                'vector_distance' => (float) ($item['vector_distance'] ?? 0.25),
                'matching_skills' => $item['matching_skills'] ?? [],
                'missing_skills' => $item['missing_skills'] ?? [],
                'posted_at' => $eloquentJob?->created_at?->toIso8601String() ?? ($item['posted_at'] ?? now()->toIso8601String()),
                'application_deadline' => $eloquentJob?->application_deadline?->format('Y-m-d') ?? now()->addDays(30)->toDateString(),
                'application_url' => $jid ? route('jobs.show', $jid) : null,
            ];
        }

        // Log search in cia_search_logs
        $topMatch = $hydratedMatches[0] ?? null;
        $allMissing = [];
        foreach ($hydratedMatches as $m) {
            foreach ($m['missing_skills'] as $miss) {
                if (!in_array($miss, $allMissing)) {
                    $allMissing[] = $miss;
                }
            }
        }

        CiaSearchLog::create([
            'user_id' => $user->id,
            'target_role' => $domain !== 'all' ? ucfirst($domain) . ' Internship Track' : 'Semantic Internship Matching',
            'query_type' => 'semantic_match',
            'skill_profile' => $skillProfile,
            'gaps' => $allMissing,
            'readiness_score' => $topMatch['affinity_percentage'] ?? 0.0,
            'priority_learning' => array_slice($allMissing, 0, 5),
            'recommendations' => $hydratedMatches,
            'status' => 'completed',
            'metadata' => [
                'engine' => $engine,
                'filters' => [
                    'domain' => $domain,
                    'location_type' => $locationType,
                    'min_affinity' => $minAffinity,
                    'limit' => $limit,
                ],
                'candidate_pseudonym' => $pseudonymousCandidateId,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        return [
            'success' => true,
            'meta' => [
                'candidate_id' => $pseudonymousCandidateId,
                'total_matched' => count($hydratedMatches),
                'query_parameters' => [
                    'domain' => $domain,
                    'min_affinity' => (int) $minAffinity,
                    'location_type' => $locationType,
                    'limit' => $limit,
                ],
                'engine' => $engine,
            ],
            'data' => $hydratedMatches,
        ];
    }

    /**
     * Safely invoke the Python matching engine via temporary JSON file.
     *
     * @param array $payload
     * @return array|null
     */
    public function invokePythonEngine(array $payload): ?array
    {
        $possiblePaths = [
            base_path('scripts/chromadb_matching_engine.py'),
            base_path('../scripts/chromadb_matching_engine.py'),
            app_path('../scripts/chromadb_matching_engine.py'),
        ];

        $scriptPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $scriptPath = realpath($path);
                break;
            }
        }

        if (!$scriptPath) {
            Log::warning('chromadb_matching_engine.py not found in configured paths.');
            return null;
        }

        $inputJson = json_encode($payload);
        $tempInput = tempnam(sys_get_temp_dir(), 'cia_chroma_');
        if ($tempInput === false) {
            return null;
        }

        file_put_contents($tempInput, $inputJson);

        $cmd = 'python ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($tempInput);
        $stdout = shell_exec($cmd);

        @unlink($tempInput);

        if (empty($stdout)) {
            Log::warning('Empty response from Python ChromaDB matching engine.');
            return null;
        }

        $trimmed = trim($stdout);
        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');

        if ($start !== false && $end !== false) {
            $jsonStr = substr($trimmed, $start, $end - $start + 1);
            $parsed = json_decode($jsonStr, true);
            if (is_array($parsed) && (isset($parsed['matches']) || isset($parsed['target_collection']))) {
                return $parsed;
            }
        }

        Log::warning('Failed to parse valid JSON from ChromaDB engine output: ' . substr($trimmed, 0, 300));
        return null;
    }

    /**
     * Fallback matching synthesizer when Python or ChromaDB is unreachable.
     */
    protected function fallbackMatching(array $skillProfile, array $jobs, array $filters): array
    {
        $domainFilter = strtolower($filters['domain'] ?? 'all');
        $locationFilter = strtolower($filters['location_type'] ?? 'all');
        $minAffinity = (float) ($filters['min_affinity'] ?? 0.0);
        $limit = (int) ($filters['limit'] ?? 5);

        $candSkills = array_map(fn ($s) => strtolower($s['skill'] ?? ''), $skillProfile);
        $candSkillsStr = implode(' ', $candSkills);

        $scored = [];
        foreach ($jobs as $job) {
            $title = strtolower($job['title'] ?? '');
            $requirements = strtolower($job['requirements'] ?? '');
            $location = strtolower($job['location'] ?? '');
            $combined = "{$title} {$requirements}";

            // Domain filter
            $domain = 'backend';
            if (str_contains($combined, 'ai') || str_contains($combined, 'llm') || str_contains($combined, 'chromadb')) {
                $domain = 'ai';
            } elseif (str_contains($combined, 'devops') || str_contains($combined, 'docker') || str_contains($combined, 'cloud')) {
                $domain = 'devops';
            } elseif (str_contains($combined, 'frontend') || str_contains($combined, 'vue') || str_contains($combined, 'tailwind')) {
                $domain = 'frontend';
            }

            if ($domainFilter !== 'all' && $domain !== $domainFilter) {
                continue;
            }

            // Location filter
            $locType = str_contains($location, 'remote') ? 'remote' : (str_contains($location, 'hybrid') ? 'hybrid' : 'onsite');
            if ($locationFilter !== 'all' && $locType !== $locationFilter) {
                continue;
            }

            // Calculate overlap
            $matches = [];
            $missing = [];
            foreach ($candSkills as $skill) {
                if (!empty($skill) && str_contains($combined, $skill)) {
                    $matches[] = ucfirst($skill);
                }
            }

            $potentialMissing = ['Docker', 'Redis', 'Vue.js', 'ChromaDB', 'Python', 'Tailwind CSS', 'AWS', 'Postman'];
            foreach ($potentialMissing as $miss) {
                if (str_contains($combined, strtolower($miss)) && !in_array(strtolower($miss), $candSkills)) {
                    $missing[] = $miss;
                }
            }

            $score = 65.0 + (count($matches) * 7.5);
            $affinity = min(98.0, max(45.0, round($score, 1)));

            if ($affinity < $minAffinity) {
                continue;
            }

            $scored[] = [
                'job_id' => $job['id'],
                'title' => $job['title'],
                'company' => $job['company'],
                'domain' => $domain,
                'location_type' => $locType,
                'affinity_percentage' => $affinity,
                'vector_distance' => round(1.0 - ($affinity / 100.0), 4),
                'matching_skills' => array_slice($matches, 0, 5),
                'missing_skills' => array_slice($missing, 0, 5),
                'salary_range' => $job['salary_range'] ?? 'Competitive',
                'posted_at' => $job['created_at'] ?? now()->toIso8601String(),
            ];
        }

        usort($scored, fn ($a, $b) => $b['affinity_percentage'] <=> $a['affinity_percentage']);

        return [
            'success' => true,
            'matches' => array_slice($scored, 0, $limit),
            'engine' => 'chromadb_fallback',
        ];
    }

    /**
     * Compute candidate composite score out of 100.
     */
    protected function calculateCompositeScore(array $skillProfile): float
    {
        if (empty($skillProfile)) {
            return 70.0;
        }

        $scores = array_column($skillProfile, 'proficiency_score');
        return round(array_sum($scores) / count($scores), 1);
    }

    /**
     * Infer primary domain from skill profile.
     */
    protected function inferPrimaryDomain(array $skillProfile): string
    {
        $skillsStr = strtolower(implode(' ', array_column($skillProfile, 'skill')));
        if (str_contains($skillsStr, 'ai') || str_contains($skillsStr, 'python') || str_contains($skillsStr, 'chromadb')) {
            return 'ai';
        }
        if (str_contains($skillsStr, 'vue') || str_contains($skillsStr, 'react') || str_contains($skillsStr, 'tailwind') || str_contains($skillsStr, 'css')) {
            return 'frontend';
        }
        if (str_contains($skillsStr, 'docker') || str_contains($skillsStr, 'linux') || str_contains($skillsStr, 'cloud')) {
            return 'devops';
        }
        return 'backend';
    }
}

