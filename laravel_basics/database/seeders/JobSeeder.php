<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtain primary test user or create one
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '+1 (555) 123-4567',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $recruiterUser = User::firstOrCreate(
            ['email' => 'recruiter@cynaris.com'],
            [
                'name' => 'Cynaris Talent Partner',
                'phone' => '+1 (555) 987-6543',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $demoCandidate = User::firstOrCreate(
            ['email' => 'candidate@example.com'],
            [
                'name' => 'Alex Candidate',
                'phone' => '+1 (555) 456-7890',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $curatedJobs = [
            [
                'user_id' => $recruiterUser->id,
                'title' => 'Senior Laravel & Cloud Architect',
                'company' => 'Cynaris Systems',
                'location' => 'Bengaluru, India (Hybrid)',
                'employment_type' => 'Full-time',
                'description' => "Join Cynaris Systems as our Lead Laravel & Cloud Architect. You will shape our flagship enterprise SaaS microservices, orchestrate containerized deployments on modern cloud infrastructure, and mentor junior engineers.\n\nKey Responsibilities:\n- Design scalable domain architectures using Laravel 11/13, Eloquent, and Sanctum.\n- Guide database partitioning, Redis caching topologies, and event-driven queues.\n- Drive test-driven development (TDD) best practices with automated CI/CD pipelines.\n- Work with enterprise customers to build secure, robust RESTful APIs.",
                'requirements' => "- 4+ years of professional backend engineering experience with PHP 8.2+ and Laravel.\n- Strong expertise in relational database design (PostgreSQL/MySQL/SQLite) and query profiling.\n- Hands-on experience with Docker, Redis, and container orchestration.\n- Strong communication skills and demonstrated mentorship ability.",
                'salary_range' => '₹18,00,000 - ₹26,00,000 / yr',
                'application_deadline' => now()->addDays(30)->toDateString(),
                'status' => 'active',
            ],
            [
                'user_id' => $testUser->id,
                'title' => 'Full Stack PHP & Tailwind Engineer',
                'company' => 'NovaCore Digital Labs',
                'location' => 'Remote (Global)',
                'employment_type' => 'Remote',
                'description' => "NovaCore Digital is expanding its core platform engineering squad. We seek an agile Full Stack Engineer fluent in Laravel, Blade, Vite, and Tailwind CSS.\n\nKey Responsibilities:\n- Deliver customer-facing features from interactive Blade templates to Eloquent repositories.\n- Create responsive, pixel-perfect user interfaces adhering to modern web design standards.\n- Maintain high code coverage with PHPUnit feature and integration tests.\n- Participate in bi-weekly sprint reviews and code refactoring sessions.",
                'requirements' => "- 2+ years of full-stack web development experience with Laravel and Tailwind CSS.\n- Strong comprehension of Alpine.js, Vanilla JavaScript, and REST APIs.\n- Ability to work effectively in an asynchronous, remote-first environment.\n- Bachelor's degree in Computer Science, Software Engineering, or equivalent practical experience.",
                'salary_range' => '$85,000 - $115,000 / yr',
                'application_deadline' => now()->addDays(45)->toDateString(),
                'status' => 'active',
            ],
            [
                'user_id' => $recruiterUser->id,
                'title' => 'Graduate Software Engineer — Laravel Intern',
                'company' => 'Cynaris Systems',
                'location' => 'Pune, India (On-site)',
                'employment_type' => 'Internship',
                'description' => "Kickstart your professional software engineering journey with the Cynaris Systems Internship Program. You will receive direct mentorship from seasoned architects while contributing real production code.\n\nKey Responsibilities:\n- Complete weekly sprint assignments covering MVC design, REST APIs, and database migrations.\n- Implement feature enhancements, bug fixes, and PHPUnit test cases.\n- Participate in daily standups and engineering design walkthroughs.",
                'requirements' => "- Sound fundamentals in Object-Oriented Programming (OOP) and relational databases.\n- Familiarity with PHP, Laravel basics, HTML5, CSS3, and JavaScript.\n- Eagerness to learn, ask thoughtful questions, and receive constructive code reviews.\n- Recent graduate or final year student in CS, IT, or related technical disciplines.",
                'salary_range' => '₹30,000 - ₹45,000 / mo (Stipend)',
                'application_deadline' => now()->addDays(20)->toDateString(),
                'status' => 'active',
            ],
            [
                'user_id' => $testUser->id,
                'title' => 'DevOps & SRE Specialist (Railway / AWS)',
                'company' => 'Apex Cloud Solutions',
                'location' => 'Hyderabad, India (Hybrid)',
                'employment_type' => 'Full-time',
                'description' => "Apex Cloud is seeking an experienced Site Reliability Engineer to automate our multi-cloud deployment pipelines, monitor system availability, and enforce enterprise cybersecurity policies.\n\nKey Responsibilities:\n- Configure and optimize automated deployment workflows on platforms like Railway and AWS.\n- Implement application performance monitoring, structured logging, and automated alerting.\n- Secure database backups, SSL/TLS certificates, and environment secrets management.",
                'requirements' => "- 3+ years experience in DevOps, Linux systems administration, and cloud deployment.\n- Proficiency in shell scripting, Git, Docker, and CI/CD pipelines.\n- Experience configuring web servers (Nginx, Caddy, Apache) and PHP-FPM.",
                'salary_range' => '₹14,00,000 - ₹20,00,000 / yr',
                'application_deadline' => now()->addDays(35)->toDateString(),
                'status' => 'active',
            ],
            [
                'user_id' => $recruiterUser->id,
                'title' => 'REST API & Microservices Developer',
                'company' => 'HyperScale Payments',
                'location' => 'Mumbai, India (Hybrid)',
                'employment_type' => 'Contract',
                'description' => "Join our payments engineering squad on a 6-month extendable contract to build next-generation payment gateway integrations, webhook processors, and high-throughput financial transaction ledgers.\n\nKey Responsibilities:\n- Build secure RESTful API endpoints secured by Laravel Sanctum Bearer tokens.\n- Implement idempotent transaction workflows, rate limiting, and audit trails.\n- Write rigorous unit and feature tests validating error boundaries and status codes.",
                'requirements' => "- 3+ years of experience with REST API architecture and token-based authentication.\n- Experience handling financial calculations, floating-point precision, and ACID transactions.\n- Deep familiarity with Postman, OpenAPI/Swagger specifications, and PHPUnit.",
                'salary_range' => '₹1,20,000 - ₹1,60,000 / mo',
                'application_deadline' => now()->addDays(25)->toDateString(),
                'status' => 'active',
            ],
            [
                'user_id' => $recruiterUser->id,
                'title' => 'Frontend UI/UX Designer & Blade Developer',
                'company' => 'Vanguard Software Studios',
                'location' => 'Remote (India)',
                'employment_type' => 'Part-time',
                'description' => "Vanguard Studios creates premium web experiences for modern startups. We are seeking a design-first engineer who combines aesthetic mastery with solid Blade and Tailwind implementation skills.\n\nKey Responsibilities:\n- Create high-fidelity design mockups and translate them into responsive Blade views.\n- Ensure strict accessibility (WCAG AA), responsive breakpoints, and micro-animations.\n- Refine typographic hierarchies, color schemes, and component design systems.",
                'requirements' => "- Proven portfolio demonstrating visual excellence in UI/UX web design.\n- Mastery of Tailwind CSS, CSS Grid/Flexbox, and semantic HTML5.\n- Experience working with Figma and integrating designs into Laravel Blade templates.",
                'salary_range' => '₹40,000 - ₹60,000 / mo',
                'application_deadline' => now()->addDays(40)->toDateString(),
                'status' => 'active',
            ],
        ];

        foreach ($curatedJobs as $jobData) {
            $job = Job::firstOrCreate(
                ['title' => $jobData['title'], 'company' => $jobData['company']],
                $jobData
            );

            // Create sample application from testUser to one job (if not already applied)
            if ($job->title === 'Senior Laravel & Cloud Architect') {
                Application::firstOrCreate(
                    [
                        'job_id' => $job->id,
                        'user_id' => $testUser->id,
                    ],
                    [
                        'cover_letter' => "Hello Cynaris Recruiting Team,\n\nI am thrilled to apply for the Senior Laravel & Cloud Architect role. Having built full-stack Laravel solutions throughout the Cynaris Internship and demonstrated mastery of Eloquent, Sanctum, and PHPUnit, I am eager to contribute immediately.\n\nBest regards,\nTest User",
                        'resume_url' => 'https://linkedin.com/in/test-user-dev',
                        'status' => 'reviewed',
                    ]
                );
            }

            // Create sample application from demoCandidate
            if ($job->title === 'Full Stack PHP & Tailwind Engineer') {
                Application::firstOrCreate(
                    [
                        'job_id' => $job->id,
                        'user_id' => $demoCandidate->id,
                    ],
                    [
                        'cover_letter' => "Dear NovaCore Hiring Team,\n\nI am excited to submit my candidacy for the Full Stack PHP & Tailwind Engineer position. My background in building responsive web applications and writing automated tests makes me an ideal fit.\n\nSincerely,\nAlex Candidate",
                        'resume_url' => 'https://github.com/alex-candidate',
                        'status' => 'pending',
                    ]
                );
            }
        }
    }
}
