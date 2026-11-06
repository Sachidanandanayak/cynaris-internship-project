<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;

class InternshipOpportunitySeeder extends Seeder
{
    /**
     * Seed 20 curated internship & early career opportunity profiles for W9D4 ChromaDB Matching.
     */
    public function run(): void
    {
        $recruiter = User::firstOrCreate(
            ['email' => 'recruiter@cynaris.com'],
            [
                'name' => 'Cynaris Talent Partner',
                'phone' => '+1 (555) 987-6543',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $internships = [
            [
                'title' => 'Graduate Software Engineer — Laravel Intern',
                'company' => 'Cynaris Systems',
                'location' => 'Pune, India (On-site)',
                'employment_type' => 'Internship',
                'description' => "Kickstart your professional software engineering journey with the Cynaris Systems Internship Program. You will receive direct mentorship from seasoned architects while contributing real production code.\n\nKey Responsibilities:\n- Complete weekly sprint assignments covering MVC design, REST APIs, and database migrations.\n- Implement feature enhancements, bug fixes, and PHPUnit test cases.\n- Participate in daily standups and engineering design walkthroughs.",
                'requirements' => "Strong fundamentals in PHP 8.x, Laravel MVC architecture, MySQL relational schema design, REST APIs, and Git version control.",
                'salary_range' => '₹30,000 - ₹45,000 / mo (Stipend)',
                'application_deadline' => now()->addDays(30)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Backend Laravel & API Services Intern',
                'company' => 'Cynaris Cloud Labs',
                'location' => 'Bengaluru, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Join our cloud core engineering squad to develop scalable RESTful microservices, optimize database queries, and implement token-based authentication.",
                'requirements' => "Required competencies: PHP, Laravel Framework, REST APIs, MySQL, Eloquent ORM, and Postman API contract testing.",
                'salary_range' => '₹35,000 - ₹50,000 / mo',
                'application_deadline' => now()->addDays(25)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Full-Stack PHP & Vue.js Developer Intern',
                'company' => 'NovaCore Digital',
                'location' => 'Remote (India)',
                'employment_type' => 'Internship',
                'description' => "Work across the full stack building interactive web applications combining Laravel backends with modern reactive Vue.js and Tailwind CSS frontends.",
                'requirements' => "Required competencies: PHP, Laravel, JavaScript (ES6+), Vue.js, Tailwind CSS, HTML5, and REST APIs.",
                'salary_range' => '₹32,000 - ₹45,000 / mo',
                'application_deadline' => now()->addDays(35)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Cloud Infrastructure & DevOps Intern',
                'company' => 'Apex Cloud Solutions',
                'location' => 'Hyderabad, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Learn automated CI/CD deployment pipelines, container orchestration, and server monitoring on Railway and AWS cloud platforms.",
                'requirements' => "Required competencies: Docker, Containerization, Git & CI/CD, GitHub Actions, Linux Shell, and Cloud Deployment.",
                'salary_range' => '₹35,000 - ₹48,000 / mo',
                'application_deadline' => now()->addDays(28)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'AI & LLM Integration Engineering Intern',
                'company' => 'Cynaris AI Research',
                'location' => 'Bengaluru, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Build next-generation agentic AI workflows, vector similarity search pipelines with ChromaDB, and Groq/Llama-3 LLM reasoning engines.",
                'requirements' => "Required competencies: Python, PHP, REST APIs, Groq AI, LlamaIndex, ChromaDB Vector Embeddings, and Prompt Engineering.",
                'salary_range' => '₹40,000 - ₹55,000 / mo',
                'application_deadline' => now()->addDays(20)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Frontend UI/UX & Tailwind CSS Intern',
                'company' => 'Vanguard Studios',
                'location' => 'Remote (Global)',
                'employment_type' => 'Internship',
                'description' => "Craft responsive, pixel-perfect user interfaces, interactive charts, and accessible component design systems for enterprise dashboards.",
                'requirements' => "Required competencies: JavaScript, HTML5, CSS3, Tailwind CSS, Chart.js, Responsive Design, and UI Accessibility.",
                'salary_range' => '₹28,000 - ₹40,000 / mo',
                'application_deadline' => now()->addDays(40)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Database Optimization & SQL Performance Intern',
                'company' => 'HyperScale Data',
                'location' => 'Mumbai, India (On-site)',
                'employment_type' => 'Internship',
                'description' => "Collaborate on database schema design, index optimization, execution plan profiling, and transactional integrity across MySQL and PostgreSQL.",
                'requirements' => "Required competencies: MySQL, Relational Database Design, SQL Query Optimization, Indexing, and Eloquent Performance.",
                'salary_range' => '₹32,000 - ₹44,000 / mo',
                'application_deadline' => now()->addDays(32)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Cybersecurity & Application Defense Intern',
                'company' => 'SecureSphere Labs',
                'location' => 'Delhi NCR, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Implement OWASP Top 10 security defenses, CSRF/XSS protection, rate limiting, and zero-trust authentication protocols.",
                'requirements' => "Required competencies: PHP Security, Laravel Sanctum, Authentication, Session Fixation Defense, and Encryption.",
                'salary_range' => '₹36,000 - ₹50,000 / mo',
                'application_deadline' => now()->addDays(30)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Microservices Architecture & REST API Intern',
                'company' => 'Cynaris Enterprise Client A',
                'location' => 'Pune, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Build decoupled API gateways, asynchronous job workers, and webhook notification handlers using Laravel and Redis.",
                'requirements' => "Required competencies: PHP 8, Laravel, REST APIs, JSON Schema Validation, Redis Caching, and Message Queues.",
                'salary_range' => '₹34,000 - ₹46,000 / mo',
                'application_deadline' => now()->addDays(27)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'QA Automation & PHPUnit Testing Intern',
                'company' => 'QualityCraft Software',
                'location' => 'Chennai, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Develop comprehensive unit and feature test suites with PHPUnit, ensuring zero-regression continuous integration on GitHub Actions.",
                'requirements' => "Required competencies: PHPUnit, Test-Driven Development (TDD), Feature Testing, Mockery, and Continuous Integration.",
                'salary_range' => '₹28,000 - ₹38,000 / mo',
                'application_deadline' => now()->addDays(35)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Site Reliability & Containerization Intern',
                'company' => 'CloudMatrix Operations',
                'location' => 'Bengaluru, India (Remote)',
                'employment_type' => 'Internship',
                'description' => "Automate staging environments, build multi-stage Dockerfiles, and implement centralized health-check monitoring.",
                'requirements' => "Required competencies: Docker Compose, Linux Administration, Git Workflows, Bash Scripting, and Application Telemetry.",
                'salary_range' => '₹35,000 - ₹48,000 / mo',
                'application_deadline' => now()->addDays(30)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Enterprise CRM & Workflow Automation Intern',
                'company' => 'Cynaris Solutions',
                'location' => 'Pune, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Design automated business workflows, customer onboarding pipelines, and RESTful CRM integrations with EspoCRM.",
                'requirements' => "Required competencies: PHP, JavaScript, RESTful APIs, MySQL, Webhooks, and CRM Architecture.",
                'salary_range' => '₹30,000 - ₹42,000 / mo',
                'application_deadline' => now()->addDays(40)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Real-Time Telemetry & WebSockets Intern',
                'company' => 'LiveMetrics Tech',
                'location' => 'Mumbai, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Build real-time notification feeds, live dashboard counters, and telemetry metrics aggregation using WebSockets and Chart.js.",
                'requirements' => "Required competencies: JavaScript, WebSockets, PHP, Event Broadcasting, Chart.js, and Redis Pub/Sub.",
                'salary_range' => '₹32,000 - ₹45,000 / mo',
                'application_deadline' => now()->addDays(22)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Semantic Search & Vector Database Intern',
                'company' => 'Cynaris Career Intelligence',
                'location' => 'Bengaluru, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Develop semantic nearest-neighbor retrieval systems, ChromaDB vector indexing pipelines, and dense embedding matching engines.",
                'requirements' => "Required competencies: Python, ChromaDB, Cosine Similarity, Vector Search, PHP Integration, and REST APIs.",
                'salary_range' => '₹38,000 - ₹52,000 / mo',
                'application_deadline' => now()->addDays(29)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Data Pipelines & Analytics Engineering Intern',
                'company' => 'NovaCore Data Labs',
                'location' => 'Hyderabad, India (On-site)',
                'employment_type' => 'Internship',
                'description' => "Extract, transform, and aggregate intern learning metrics and assessment results into executive performance dashboards.",
                'requirements' => "Required competencies: SQL, Python, Relational Data Modeling, Analytics Aggregation, and Data Transformation.",
                'salary_range' => '₹32,000 - ₹44,000 / mo',
                'application_deadline' => now()->addDays(36)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Open Source Developer Experience Intern',
                'company' => 'OSS Foundry',
                'location' => 'Remote (Global)',
                'employment_type' => 'Internship',
                'description' => "Contribute to open developer tooling, write clear technical documentation, package reusable Composer libraries, and build CLI utilities.",
                'requirements' => "Required competencies: PHP, Composer, Git Branch Workflows, Markdown Technical Documentation, and Code Quality Linters.",
                'salary_range' => '₹30,000 - ₹40,000 / mo',
                'application_deadline' => now()->addDays(45)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Junior Full-Stack Product Engineering Intern',
                'company' => 'Cynaris Labs',
                'location' => 'Bengaluru, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Participate in agile sprint development, building candidate-facing career mode features and recruiter dashboards.",
                'requirements' => "Required competencies: PHP, Laravel, JavaScript, HTML5/CSS3, Tailwind, and MySQL Database Design.",
                'salary_range' => '₹34,000 - ₹47,000 / mo',
                'application_deadline' => now()->addDays(24)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Multi-Cloud Deployments & Kubernetes Intern',
                'company' => 'Apex Cloud Labs',
                'location' => 'Bengaluru, India (Hybrid)',
                'employment_type' => 'Internship',
                'description' => "Deploy cloud-native microservices, configure container environments, and learn Kubernetes pod management and networking.",
                'requirements' => "Required competencies: Docker, Kubernetes Fundamentals, Linux CLI, Git, and Cloud Infrastructure.",
                'salary_range' => '₹36,000 - ₹50,000 / mo',
                'application_deadline' => now()->addDays(28)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Technical Documentation & API Specs Intern',
                'company' => 'Cynaris Documentation Team',
                'location' => 'Remote (India)',
                'employment_type' => 'Internship',
                'description' => "Author comprehensive REST API contracts, architectural diagrams with Mermaid, and developer onboarding tutorials.",
                'requirements' => "Required competencies: OpenAPI/Swagger, Markdown, Technical Writing, REST API Standards, and Git Version Control.",
                'salary_range' => '₹25,000 - ₹35,000 / mo',
                'application_deadline' => now()->addDays(50)->toDateString(),
                'status' => 'active',
            ],
            [
                'title' => 'Junior Platform Reliability & Support Intern',
                'company' => 'Cynaris Systems Support',
                'location' => 'Pune, India (On-site)',
                'employment_type' => 'Internship',
                'description' => "Investigate application logs, reproduce client-reported edge cases, write test assertions, and document system bug fixes.",
                'requirements' => "Required competencies: PHP, Debugging Techniques, SQL Queries, Log Analysis, and Troubleshooting.",
                'salary_range' => '₹28,000 - ₹38,000 / mo',
                'application_deadline' => now()->addDays(30)->toDateString(),
                'status' => 'active',
            ],
        ];

        foreach ($internships as $data) {
            Job::updateOrCreate(
                [
                    'title' => $data['title'],
                    'company' => $data['company'],
                ],
                array_merge($data, ['user_id' => $recruiter->id])
            );
        }
    }
}

