<?php

namespace App\Services\Resume;

use App\Models\CiaSearchLog;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResumeAtsScoringService
{
    /**
     * Standard keyword taxonomy for technical roles.
     */
    protected array $domainKeywords = [
        'backend' => [
            'PHP', 'Laravel', 'MySQL', 'REST APIs', 'Eloquent ORM', 'Git',
            'Redis', 'PHPUnit', 'Docker', 'Composer', 'PostgreSQL', 'MVC Architecture',
            'Authentication', 'Sanctum', 'Microservices', 'CI/CD', 'Linux'
        ],
        'frontend' => [
            'JavaScript', 'TypeScript', 'Vue.js', 'React', 'Tailwind CSS',
            'HTML5', 'CSS3', 'REST APIs', 'Vite', 'Responsive Design',
            'UI/UX', 'State Management', 'Pinia', 'Redux', 'Git'
        ],
        'fullstack' => [
            'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'MySQL', 'REST APIs',
            'Tailwind CSS', 'Docker', 'Git', 'PHPUnit', 'HTML5', 'CSS3'
        ],
        'devops' => [
            'Docker', 'Kubernetes', 'CI/CD', 'GitHub Actions', 'AWS',
            'Linux', 'Bash', 'Terraform', 'Nginx', 'Prometheus', 'Git'
        ],
        'ai' => [
            'Python', 'ChromaDB', 'Vector Embeddings', 'Groq', 'LLMs',
            'LlamaIndex', 'LangGraph', 'Prompt Engineering', 'REST APIs', 'PHP'
        ],
    ];

    /**
     * Analyze resume text against target role and optional job description.
     *
     * @param User $candidate
     * @param string $resumeText
     * @param string $targetRole
     * @param Job|null $job
     * @return array
     */
    public function scoreResume(User $candidate, string $resumeText, string $targetRole, ?Job $job = null): array
    {
        $targetRole = trim($targetRole) ?: ($job?->title ?? 'Junior Backend Laravel Engineer');
        $jobContext = $job ? "Title: {$job->title}\nCompany: {$job->company}\nRequirements: {$job->requirements}\nDescription: {$job->description}" : "Target Role: {$targetRole}";

        // Attempt Groq API analysis if configured
        $apiKey = config('services.groq.key');
        if (!empty($apiKey)) {
            try {
                $groqResult = $this->queryGroq($resumeText, $targetRole, $jobContext);
                if ($groqResult !== null && $this->validateAtsPayload($groqResult)) {
                    return $this->finalizeAndPersist($candidate, $targetRole, $job, $groqResult, 'groq_llama3');
                }
            } catch (\Throwable $e) {
                Log::warning('Groq ATS scoring failed, falling back to deterministic engine: ' . $e->getMessage(), [
                    'candidate_id' => $candidate->id,
                    'target_role' => $targetRole,
                ]);
            }
        }

        // Deterministic Fallback ATS Scoring Engine
        $fallbackResult = $this->deterministicAtsScoring($resumeText, $targetRole, $job);
        return $this->finalizeAndPersist($candidate, $targetRole, $job, $fallbackResult, 'deterministic_ats_fallback');
    }

    /**
     * Query Groq AI with strict JSON format enforcement.
     */
    protected function queryGroq(string $resumeText, string $targetRole, string $jobContext): ?array
    {
        $apiKey = config('services.groq.key');
        $model = config('services.groq.model', 'llama-3.3-70b-versatile');
        $endpoint = config('services.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');
        $timeout = (int) config('services.groq.timeout', 12);

        $systemPrompt = "You are CIA ATS Resume Intelligence, an enterprise Applicant Tracking System scoring engine for Cynaris Solutions. " .
            "Analyze the candidate resume against the target role and job description. " .
            "Calculate 4 category scores strictly from 0 to 100: " .
            "1. keyword_matching: Percentage of required skills/technologies explicitly present. " .
            "2. formatting: Professional layout, clear section headings, bulleted action statements, standard fonts, no tables/graphics. " .
            "3. work_experience: Demonstrated project execution, internship tenure, quantified outcomes, and problem-solving impact. " .
            "4. technical_skills: Depth of modern programming languages, frameworks, databases, and testing tools. " .
            "Calculate overall_ats_score using: (keyword_matching * 0.35) + (technical_skills * 0.35) + (work_experience * 0.20) + (formatting * 0.10). " .
            "Identify matched_keywords, missing_keywords, 3-5 constructive improvement_suggestions, and 2-3 section_rewrites showing current weaknesses and enhanced bullet points. " .
            "CRITICAL: Return ONLY valid JSON. Do not include markdown codeblocks, preambles, or conversational text.";

        $userPrompt = "Target Role & Job Description:\n{$jobContext}\n\nCandidate Extracted Resume Text:\n" . substr($resumeText, 0, 4000);

        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            Log::warning('Groq HTTP request returned non-200: ' . $response->status(), [
                'body' => substr($response->body(), 0, 200),
            ]);
            return null;
        }

        $body = $response->json();
        $content = $body['choices'][0]['message']['content'] ?? null;
        if (empty($content)) {
            return null;
        }

        $cleanJson = trim($content);
        if (str_starts_with($cleanJson, '```')) {
            $cleanJson = preg_replace('/^```(?:json)?/i', '', $cleanJson);
            $cleanJson = preg_replace('/```$/', '', $cleanJson);
            $cleanJson = trim($cleanJson);
        }

        $decoded = json_decode($cleanJson, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Deterministic heuristic ATS Scoring Engine.
     */
    public function deterministicAtsScoring(string $resumeText, string $targetRole, ?Job $job = null): array
    {
        $textLower = strtolower($resumeText);
        $roleLower = strtolower($targetRole);

        // Determine domain
        $domain = 'backend';
        if (str_contains($roleLower, 'front') || str_contains($roleLower, 'ui') || str_contains($roleLower, 'vue')) {
            $domain = 'frontend';
        } elseif (str_contains($roleLower, 'full') || str_contains($roleLower, 'stack')) {
            $domain = 'fullstack';
        } elseif (str_contains($roleLower, 'devops') || str_contains($roleLower, 'cloud')) {
            $domain = 'devops';
        } elseif (str_contains($roleLower, 'ai') || str_contains($roleLower, 'llm') || str_contains($roleLower, 'chroma')) {
            $domain = 'ai';
        }

        $expectedKeywords = $this->domainKeywords[$domain] ?? $this->domainKeywords['backend'];
        if ($job && !empty($job->requirements)) {
            // Incorporate job requirements into keywords
            preg_match_all('/[A-Za-z0-9\.\+#\-]{2,}/', $job->requirements, $m);
            if (!empty($m[0])) {
                foreach ($m[0] as $kw) {
                    if (strlen($kw) > 2 && !in_array(ucfirst($kw), $expectedKeywords)) {
                        $expectedKeywords[] = ucfirst($kw);
                    }
                }
            }
        }

        $matched = [];
        $missing = [];

        foreach ($expectedKeywords as $kw) {
            $kwLower = strtolower($kw);
            if (str_contains($textLower, $kwLower)) {
                $matched[] = $kw;
            } else {
                $missing[] = $kw;
            }
        }

        $keywordRatio = count($expectedKeywords) > 0 ? (count($matched) / count($expectedKeywords)) : 0.5;
        $keywordScore = round(min(100.0, max(25.0, $keywordRatio * 100.0)), 1);

        // Technical skills score: count of verified technical terms
        $techCount = count($matched);
        $technicalScore = round(min(100.0, max(30.0, 40.0 + ($techCount * 5.0))), 1);

        // Formatting score: check standard section headers, word count
        $hasEducation = (bool) preg_match('/(education|academic|bachelor|degree|university|college)/i', $resumeText);
        $hasExperience = (bool) preg_match('/(experience|projects|work|internship|employment)/i', $resumeText);
        $hasSkills = (bool) preg_match('/(skills|technologies|proficiencies|competencies)/i', $resumeText);
        $hasContact = (bool) preg_match('/(@|[0-9]{10}|github|linkedin)/i', $resumeText);

        $wordCount = str_word_count($resumeText);
        $lengthOk = ($wordCount >= 100 && $wordCount <= 1200);

        $formattingPoints = 50.0;
        if ($hasEducation) $formattingPoints += 12.5;
        if ($hasExperience) $formattingPoints += 12.5;
        if ($hasSkills) $formattingPoints += 12.5;
        if ($hasContact) $formattingPoints += 12.5;
        if (!$lengthOk) $formattingPoints -= 15.0;

        $formattingScore = round(min(100.0, max(30.0, $formattingPoints)), 1);

        // Work experience score: check presence of action verbs, metrics, project descriptions
        $actionVerbs = ['developed', 'implemented', 'designed', 'built', 'created', 'optimized', 'managed', 'refactored', 'architected', 'integrated'];
        $actionVerbMatches = 0;
        foreach ($actionVerbs as $verb) {
            if (str_contains($textLower, $verb)) {
                $actionVerbMatches++;
            }
        }

        // Metrics presence (numbers, %, ms, users)
        $hasMetrics = (bool) preg_match('/([0-9]+%|[0-9]+\s*(ms|seconds|users|clients|records))/i', $resumeText);
        $experiencePoints = 55.0 + ($actionVerbMatches * 4.0) + ($hasMetrics ? 15.0 : 0.0);
        $experienceScore = round(min(100.0, max(30.0, $experiencePoints)), 1);

        // Overall ATS Score calculation
        $overallScore = round(
            ($keywordScore * 0.35) +
            ($technicalScore * 0.35) +
            ($experienceScore * 0.20) +
            ($formattingScore * 0.10),
            1
        );

        $suggestions = [];
        if (!empty($missing)) {
            $suggestions[] = "Incorporate missing high-priority target role keywords: " . implode(', ', array_slice($missing, 0, 4)) . ".";
        }
        if (!$hasMetrics) {
            $suggestions[] = "Quantify your achievements with concrete metrics (e.g., 'reduced API query latency by 35%', 'handled 5,000 requests/sec').";
        }
        if ($formattingScore < 80) {
            $suggestions[] = "Standardize section headings: use clear 'Professional Experience', 'Technical Skills', and 'Education' headers.";
        }
        $suggestions[] = "Tailor your professional summary to highlight alignment with {$targetRole} responsibilities.";

        $sectionRewrites = [
            [
                'section' => 'Professional Experience / Projects',
                'current_weakness' => 'Passive job descriptions lacking quantified business impact and engineering metrics.',
                'suggested_rewrite' => 'Architected and deployed RESTful microservices in Laravel 12 & PHP 8.4, reducing payload latency by 42% and implementing automated PHPUnit regression suites with >= 85% branch coverage.',
            ],
            [
                'section' => 'Technical Competencies',
                'current_weakness' => 'Uncategorized skill lists without proficiency classification or framework versioning.',
                'suggested_rewrite' => 'Backend & Databases: PHP 8.x, Laravel Framework, MySQL, Redis, REST APIs, Eloquent ORM. DevOps: Docker, Docker Compose, Git, GitHub Actions CI/CD.',
            ],
        ];

        return [
            'overall_ats_score' => $overallScore,
            'scores' => [
                'keyword_matching' => $keywordScore,
                'formatting' => $formattingScore,
                'work_experience' => $experienceScore,
                'technical_skills' => $technicalScore,
            ],
            'matched_keywords' => array_slice($matched, 0, 15),
            'missing_keywords' => array_slice($missing, 0, 10),
            'improvement_suggestions' => $suggestions,
            'section_rewrites' => $sectionRewrites,
        ];
    }

    /**
     * Validate payload returned from Groq.
     */
    protected function validateAtsPayload(array $payload): bool
    {
        if (!isset($payload['overall_ats_score']) && !isset($payload['scores'])) {
            return false;
        }

        if (isset($payload['scores'])) {
            $s = $payload['scores'];
            if (!isset($s['keyword_matching']) || !isset($s['formatting']) || !isset($s['work_experience']) || !isset($s['technical_skills'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalize, validate, and persist ATS scan results to cia_search_logs.
     */
    protected function finalizeAndPersist(User $candidate, string $targetRole, ?Job $job, array $data, string $engine): array
    {
        $scores = $data['scores'] ?? [];
        $keywordMatching = (float) ($scores['keyword_matching'] ?? 70.0);
        $formatting = (float) ($scores['formatting'] ?? 75.0);
        $workExperience = (float) ($scores['work_experience'] ?? 65.0);
        $technicalSkills = (float) ($scores['technical_skills'] ?? 75.0);

        // Enforce 0-100 clamping
        $keywordMatching = min(100.0, max(0.0, $keywordMatching));
        $formatting = min(100.0, max(0.0, $formatting));
        $workExperience = min(100.0, max(0.0, $workExperience));
        $technicalSkills = min(100.0, max(0.0, $technicalSkills));

        $overallScore = isset($data['overall_ats_score'])
            ? (float) $data['overall_ats_score']
            : round(($keywordMatching * 0.35) + ($technicalSkills * 0.35) + ($workExperience * 0.20) + ($formatting * 0.10), 1);

        $overallScore = min(100.0, max(0.0, $overallScore));

        $matchedKeywords = (array) ($data['matched_keywords'] ?? []);
        $missingKeywords = (array) ($data['missing_keywords'] ?? []);
        $suggestions = (array) ($data['improvement_suggestions'] ?? []);
        $sectionRewrites = (array) ($data['section_rewrites'] ?? []);

        // Persist scan history to cia_search_logs
        $log = CiaSearchLog::create([
            'user_id' => $candidate->id,
            'target_role' => $targetRole,
            'query_type' => 'resume_ats_score',
            'skill_profile' => $matchedKeywords,
            'gaps' => $missingKeywords,
            'readiness_score' => $overallScore,
            'priority_learning' => $suggestions,
            'recommendations' => $sectionRewrites,
            'status' => 'completed',
            'metadata' => [
                'engine' => $engine,
                'target_job_id' => $job?->id,
                'scores' => [
                    'keyword_matching' => $keywordMatching,
                    'formatting' => $formatting,
                    'work_experience' => $workExperience,
                    'technical_skills' => $technicalSkills,
                ],
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        return [
            'success' => true,
            'target_role' => $targetRole,
            'target_job_id' => $job?->id,
            'overall_ats_score' => $overallScore,
            'scores' => [
                'keyword_matching' => $keywordMatching,
                'formatting' => $formatting,
                'work_experience' => $workExperience,
                'technical_skills' => $technicalSkills,
            ],
            'matched_keywords' => $matchedKeywords,
            'missing_keywords' => $missingKeywords,
            'improvement_suggestions' => $suggestions,
            'section_rewrites' => $sectionRewrites,
            'engine' => $engine,
            'log_id' => $log->id,
        ];
    }
}
