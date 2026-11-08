<?php

namespace App\Services\Vector;

use App\Models\CiaSearchLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CiaMemoryLayerService
{
    /**
     * Curated 20 Technical Role Profiles fallback dataset.
     */
    public const ROLES_DATASET = [
        [
            'id' => 'role_01',
            'role' => 'Junior Backend Laravel Engineer',
            'domain' => 'backend',
            'experience_level' => 'Junior',
            'required_skills' => ['PHP', 'Laravel', 'MySQL', 'REST APIs', 'Git', 'MVC Architecture'],
            'match_criteria' => 'Solid understanding of Laravel routing, Eloquent ORM, database migrations, and basic authentication.',
        ],
        [
            'id' => 'role_02',
            'role' => 'Senior Backend Systems Architect',
            'domain' => 'backend',
            'experience_level' => 'Senior',
            'required_skills' => ['PHP', 'Laravel', 'MySQL', 'Redis', 'Docker', 'REST APIs', 'Microservices', 'PHPUnit'],
            'match_criteria' => 'Advanced system scalability, database indexing, caching strategies, and resilient microservices design.',
        ],
        [
            'id' => 'role_03',
            'role' => 'Mid-Level Full Stack Engineer',
            'domain' => 'fullstack',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['PHP', 'Laravel', 'JavaScript', 'Vue.js', 'Tailwind CSS', 'MySQL', 'REST APIs', 'Git'],
            'match_criteria' => 'Seamless capability bridging frontend reactivity with backend API controllers and database transactions.',
        ],
        [
            'id' => 'role_04',
            'role' => 'AI Application & Vector Pipeline Engineer',
            'domain' => 'ai',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['Python', 'ChromaDB', 'Vector Embeddings', 'Groq', 'LLMs', 'LlamaIndex', 'REST APIs', 'PHP'],
            'match_criteria' => 'Implementation of semantic vector retrieval, RAG architectures, prompt pipelines, and embedding synchronization.',
        ],
        [
            'id' => 'role_05',
            'role' => 'Agentic Workflows Specialist (LangGraph & CrewAI)',
            'domain' => 'ai',
            'experience_level' => 'Senior',
            'required_skills' => ['Python', 'LangGraph', 'CrewAI', 'StateGraph', 'Groq', 'Multi-Agent Systems', 'Vector Embeddings'],
            'match_criteria' => 'Multi-agent coordination, deterministic state machines, cyclic DAG execution, and tool delegation.',
        ],
        [
            'id' => 'role_06',
            'role' => 'DevOps & Cloud Infrastructure Engineer',
            'domain' => 'devops',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['Docker', 'Kubernetes', 'CI/CD', 'GitHub Actions', 'Linux', 'AWS', 'Bash', 'Nginx'],
            'match_criteria' => 'Automated containerized delivery pipelines, continuous integration, infrastructure as code, and server hardening.',
        ],
        [
            'id' => 'role_07',
            'role' => 'Junior Frontend Vue.js Developer',
            'domain' => 'frontend',
            'experience_level' => 'Junior',
            'required_skills' => ['JavaScript', 'Vue.js', 'Tailwind CSS', 'HTML5', 'CSS3', 'REST APIs', 'Vite', 'Git'],
            'match_criteria' => 'Responsive UI component authoring, state management, reactive DOM updates, and clean CSS styling.',
        ],
        [
            'id' => 'role_08',
            'role' => 'Senior Frontend Architect',
            'domain' => 'frontend',
            'experience_level' => 'Senior',
            'required_skills' => ['JavaScript', 'TypeScript', 'Vue.js', 'React', 'Tailwind CSS', 'Vite', 'Pinia', 'UI/UX Design'],
            'match_criteria' => 'Enterprise frontend architecture, component libraries, performance optimization, and accessibility.',
        ],
        [
            'id' => 'role_09',
            'role' => 'Database Administrator & SQL Optimization Specialist',
            'domain' => 'database',
            'experience_level' => 'Senior',
            'required_skills' => ['MySQL', 'PostgreSQL', 'SQL Query Optimization', 'Indexing', 'Database Migrations', 'Redis', 'PHP'],
            'match_criteria' => 'Complex relational modeling, execution plan profiling, replication, indexing strategies, and data consistency.',
        ],
        [
            'id' => 'role_10',
            'role' => 'Application Security & OWASP Engineer',
            'domain' => 'security',
            'experience_level' => 'Senior',
            'required_skills' => ['Application Security', 'OWASP Top 10', 'Authentication', 'Sanctum', 'CSRF', 'Penetration Testing', 'PHP'],
            'match_criteria' => 'Secure coding standards, token lifecycle management, input sanitization, vulnerability remediation.',
        ],
        [
            'id' => 'role_11',
            'role' => 'API Platform & Integration Engineer',
            'domain' => 'backend',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['REST APIs', 'PHP', 'Laravel', 'Postman', 'OpenAPI', 'Swagger', 'Webhooks', 'JSON'],
            'match_criteria' => 'High-throughput idempotent REST endpoints, API versioning, third-party webhook integrations, rate limiting.',
        ],
        [
            'id' => 'role_12',
            'role' => 'Quality Assurance & Automated Test Engineer',
            'domain' => 'qa',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['PHPUnit', 'Automated Testing', 'TDD', 'Integration Testing', 'Pest', 'CI/CD', 'Git'],
            'match_criteria' => 'Test-driven development, regression test suites, mocking external HTTP services, coverage profiling.',
        ],
        [
            'id' => 'role_13',
            'role' => 'Cloud Site Reliability Engineer (SRE)',
            'domain' => 'devops',
            'experience_level' => 'Senior',
            'required_skills' => ['Linux', 'Docker', 'AWS', 'Prometheus', 'Grafana', 'Incident Management', 'CI/CD', 'Bash'],
            'match_criteria' => 'High availability architecture, 99.9% uptime enforcement, telemetry monitoring, incident mitigation.',
        ],
        [
            'id' => 'role_14',
            'role' => 'Lead Software Engineer (Technical Mentor)',
            'domain' => 'backend',
            'experience_level' => 'Lead',
            'required_skills' => ['PHP', 'Laravel', 'System Architecture', 'Code Review', 'Technical Leadership', 'Agile', 'Git'],
            'match_criteria' => 'Architectural governance, team mentorship, rigorous PR code reviews, and strategic product delivery.',
        ],
        [
            'id' => 'role_15',
            'role' => 'Data Engineer & Analytics Pipeline Developer',
            'domain' => 'data',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['Python', 'SQL', 'MySQL', 'ETL Pipelines', 'Pandas', 'REST APIs', 'Docker'],
            'match_criteria' => 'Data extraction and transformation pipelines, aggregate telemetry reporting, database staging tables.',
        ],
        [
            'id' => 'role_16',
            'role' => 'Junior Full Stack Web Developer',
            'domain' => 'fullstack',
            'experience_level' => 'Junior',
            'required_skills' => ['PHP', 'Laravel', 'JavaScript', 'HTML5', 'CSS3', 'Tailwind CSS', 'MySQL', 'Git'],
            'match_criteria' => 'Entry-level full-stack competence building complete CRUD apps with Blade and Tailwind.',
        ],
        [
            'id' => 'role_17',
            'role' => 'AI Search & Information Retrieval Specialist',
            'domain' => 'ai',
            'experience_level' => 'Senior',
            'required_skills' => ['ChromaDB', 'Vector Search', 'Cosine Similarity', 'Python', 'Embeddings', 'Elasticsearch', 'REST APIs'],
            'match_criteria' => 'High-dimensional vector space indexing, semantic search tuning, nearest-neighbor query optimization.',
        ],
        [
            'id' => 'role_18',
            'role' => 'Microservices Backend Developer',
            'domain' => 'backend',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['PHP', 'Laravel', 'Docker', 'RabbitMQ', 'REST APIs', 'Redis', 'MySQL'],
            'match_criteria' => 'Event-driven architecture, asynchronous message queues, decoupled microservices, circuit breaker patterns.',
        ],
        [
            'id' => 'role_19',
            'role' => 'Mobile-Responsive UI/UX Engineer',
            'domain' => 'frontend',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['Tailwind CSS', 'JavaScript', 'Responsive Design', 'UI/UX', 'HTML5', 'CSS3', 'Figma'],
            'match_criteria' => 'Fluid responsive breakpoints, accessible design systems, intuitive user workflows, polished micro-interactions.',
        ],
        [
            'id' => 'role_20',
            'role' => 'Technical Documentation & Developer Relations Engineer',
            'domain' => 'docs',
            'experience_level' => 'Mid-Level',
            'required_skills' => ['Technical Writing', 'API Documentation', 'Markdown', 'Git', 'PHP', 'Laravel', 'Postman'],
            'match_criteria' => 'Clear architectural guides, developer onboarding manuals, OpenAPI schemas, and interactive code examples.',
        ],
    ];

    /**
     * Seed 20 technical role profiles into ChromaDB memory store.
     *
     * @return array
     */
    public function seedRoles(): array
    {
        $payload = [
            'action' => 'seed_roles',
            'chroma_path' => storage_path('app/chromadb'),
            'collection_name' => 'cynaris_role_memory',
        ];

        try {
            $result = $this->invokePythonEngine($payload);
            if ($result && !empty($result['success']) && isset($result['seeded_count'])) {
                return $result;
            }
        } catch (\Throwable $e) {
            Log::warning("ChromaDB role memory seeding fallback: {$e->getMessage()}");
        }

        return [
            'success' => true,
            'collection' => 'cynaris_role_memory',
            'seeded_count' => count(self::ROLES_DATASET),
            'roles' => array_column(self::ROLES_DATASET, 'role'),
            'engine' => 'deterministic_fallback',
        ];
    }

    /**
     * Search ChromaDB memory layer for top-5 matching technical role profiles.
     *
     * @param User $candidate
     * @param array<string, mixed> $options
     * @return array
     */
    public function searchRoleMatches(User $candidate, array $options = []): array
    {
        $limit = isset($options['limit']) ? (int) $options['limit'] : 5;
        $skillProfile = $candidate->buildSkillProfile();
        $pseudonymousId = 'cand_' . $candidate->id;

        $payload = [
            'action' => 'search_roles',
            'chroma_path' => storage_path('app/chromadb'),
            'collection_name' => 'cynaris_role_memory',
            'limit' => $limit,
            'candidate' => [
                'candidate_id' => $pseudonymousId,
                'skills' => $skillProfile,
            ],
        ];

        $matches = [];
        $engine = 'chromadb_fallback_engine';

        try {
            $result = $this->invokePythonEngine($payload);
            if ($result && !empty($result['success']) && !empty($result['matches'])) {
                $matches = $result['matches'];
                $engine = $result['engine'] ?? 'chromadb_memory_vector';
            }
        } catch (\Throwable $e) {
            Log::warning("ChromaDB memory search failed: {$e->getMessage()}. Using deterministic fallback.");
        }

        if (empty($matches)) {
            $matches = $this->deterministicRoleSearch($skillProfile, $limit);
            $engine = 'chromadb_fallback_engine';
        }

        $topMatch = $matches[0] ?? null;
        $topRole = $topMatch['role'] ?? 'Junior Backend Laravel Engineer';
        $topAffinity = $topMatch['affinity_percentage'] ?? 75.0;
        $allMissing = $topMatch['missing_skills'] ?? [];

        // Log search query in cia_search_logs
        CiaSearchLog::create([
            'user_id' => $candidate->id,
            'target_role' => $topRole,
            'query_type' => 'memory_role_match',
            'skill_profile' => $skillProfile,
            'gaps' => $allMissing,
            'readiness_score' => $topAffinity,
            'priority_learning' => array_slice($allMissing, 0, 5),
            'recommendations' => $matches,
            'status' => 'completed',
            'metadata' => [
                'engine' => $engine,
                'candidate_id' => $pseudonymousId,
                'total_matched' => count($matches),
                'top_role' => $topRole,
                'collection' => 'cynaris_role_memory',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        return [
            'success' => true,
            'meta' => [
                'candidate_id' => $pseudonymousId,
                'total_matched' => count($matches),
                'limit' => $limit,
                'engine' => $engine,
                'collection' => 'cynaris_role_memory',
            ],
            'data' => $matches,
        ];
    }

    /**
     * Deterministic role search fallback logic.
     *
     * @param array $skillProfile
     * @param int $limit
     * @return array
     */
    public function deterministicRoleSearch(array $skillProfile, int $limit = 5): array
    {
        $candSkills = array_map('strtolower', array_column($skillProfile, 'skill'));

        $scored = [];
        foreach (self::ROLES_DATASET as $profile) {
            $reqSkills = $profile['required_skills'];
            $matching = [];
            $missing = [];

            foreach ($reqSkills as $req) {
                $reqLower = strtolower($req);
                $has = false;
                foreach ($candSkills as $c) {
                    if (str_contains($reqLower, $c) || str_contains($c, $reqLower)) {
                        $has = true;
                        break;
                    }
                }
                if ($has) {
                    $matching[] = $req;
                } else {
                    $missing[] = $req;
                }
            }

            $totalReq = count($reqSkills) ?: 1;
            $overlapRatio = count($matching) / $totalReq;
            $affinity = round(50.0 + ($overlapRatio * 45.0), 1);
            $affinity = max(40.0, min(98.0, $affinity));
            $distance = round(1.0 - ($affinity / 100.0), 4);

            $scored[] = [
                'role_id' => $profile['id'],
                'role' => $profile['role'],
                'domain' => $profile['domain'],
                'experience_level' => $profile['experience_level'],
                'affinity_percentage' => $affinity,
                'vector_distance' => $distance,
                'matching_skills' => $matching,
                'missing_skills' => $missing,
                'match_criteria' => $profile['match_criteria'],
                'required_skills' => $reqSkills,
            ];
        }

        usort($scored, fn ($a, $b) => $b['affinity_percentage'] <=> $a['affinity_percentage']);
        return array_slice($scored, 0, $limit);
    }

    /**
     * Safely invoke the Python memory engine via temporary JSON file.
     *
     * @param array $payload
     * @return array|null
     */
    public function invokePythonEngine(array $payload): ?array
    {
        $possiblePaths = [
            base_path('scripts/chromadb_memory_engine.py'),
            base_path('../scripts/chromadb_memory_engine.py'),
            app_path('../scripts/chromadb_memory_engine.py'),
        ];

        $scriptPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $scriptPath = realpath($path);
                break;
            }
        }

        if (!$scriptPath) {
            return null;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'cia_mem_') . '.json';
        file_put_contents($tmpFile, json_encode($payload));

        $pythonBin = 'python';
        $cmd = escapeshellcmd("{$pythonBin} " . escapeshellarg($scriptPath) . " " . escapeshellarg($tmpFile));

        $output = [];
        $returnVar = 0;
        exec($cmd . ' 2>&1', $output, $returnVar);

        @unlink($tmpFile);

        $rawOutput = implode("\n", $output);
        $cleanJson = trim($rawOutput);

        // Extract JSON between first { and last }
        $firstBrace = strpos($cleanJson, '{');
        $lastBrace = strrpos($cleanJson, '}');
        if ($firstBrace !== false && $lastBrace !== false) {
            $cleanJson = substr($cleanJson, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        $decoded = json_decode($cleanJson, true);
        return is_array($decoded) ? $decoded : null;
    }
}

