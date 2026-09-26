<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Week 4 Day 5 — Full Stack Capstone Feature Tests
 *
 * Mandatory Capstone Test Cases:
 * 1. Homepage/job listing loads successfully.
 * 2. Authenticated user can create a job.
 * 3. Job details page loads.
 * 4. Authenticated user can apply for a job.
 * 5. Duplicate application is prevented.
 *
 * Additional Capstone Tests:
 * 6. Authenticated dashboard renders recruitment metrics.
 * 7. Candidate can view submitted applications.
 * 8. REST API endpoints function with Sanctum & API Resources.
 */
class JobBoardCapstoneTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Homepage and Job listing load successfully with HTTP 200.
     */
    public function test_homepage_and_job_listing_load_successfully(): void
    {
        $job = Job::factory()->create([
            'title'           => 'Principal Laravel Engineer',
            'company'         => 'Cynaris Core Labs',
            'location'        => 'Remote (India)',
            'employment_type' => 'Remote',
            'status'          => 'active',
        ]);

        // A. Root Homepage loads with Capstone references
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Full-Stack Job Board');
        $homeResponse->assertSee(route('jobs.index'));

        // B. Job Board Index loads with HTTP 200 and renders job card
        $jobResponse = $this->get(route('jobs.index'));
        $jobResponse->assertStatus(200);
        $jobResponse->assertSee('Principal Laravel Engineer');
        $jobResponse->assertSee('Cynaris Core Labs');
        $jobResponse->assertSee('Remote (India)');
        $jobResponse->assertSee('Remote');
    }

    /**
     * Test 2: Authenticated user can create a job.
     */
    public function test_authenticated_user_can_create_a_job(): void
    {
        $recruiter = User::factory()->create([
            'email' => 'hiring@cynaris.com',
        ]);

        $jobData = [
            'title'                => 'Senior Full Stack PHP Developer',
            'company'              => 'Cynaris Tech Solutions',
            'location'             => 'Bengaluru, India (Hybrid)',
            'employment_type'      => 'Full-time',
            'description'          => 'We are seeking an experienced Laravel engineer to lead backend architecture and mentor interns.',
            'requirements'         => '3+ years experience with Laravel, PHPUnit, MySQL, and Tailwind CSS.',
            'salary_range'         => '₹16,00,000 - ₹22,00,000 / yr',
            'application_deadline' => now()->addDays(30)->toDateString(),
            'status'               => 'active',
        ];

        $response = $this->actingAs($recruiter)->post(route('jobs.store'), $jobData);

        $response->assertStatus(302);

        $job = Job::where('title', 'Senior Full Stack PHP Developer')->first();
        $this->assertNotNull($job);
        $response->assertRedirect(route('jobs.show', $job));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jobs', [
            'title'           => 'Senior Full Stack PHP Developer',
            'company'         => 'Cynaris Tech Solutions',
            'user_id'         => $recruiter->id,
            'employment_type' => 'Full-time',
            'status'          => 'active',
        ]);
    }

    /**
     * Test 3: Job details page loads with comprehensive job information.
     */
    public function test_job_details_page_loads(): void
    {
        $job = Job::factory()->create([
            'title'           => 'DevOps & Site Reliability Engineer',
            'company'         => 'CloudVanguard Corp',
            'location'        => 'Pune, India',
            'employment_type' => 'Full-time',
            'description'     => 'Lead CI/CD pipeline automation and Railway deployment infrastructure.',
            'requirements'    => 'Extensive knowledge of Docker, Linux, Git, and automated testing.',
            'salary_range'    => '₹14,00,000 - ₹19,00,000 / yr',
            'status'          => 'active',
        ]);

        $response = $this->get(route('jobs.show', $job));

        $response->assertStatus(200);
        $response->assertSee('DevOps &amp; Site Reliability Engineer', false);
        $response->assertSee('CloudVanguard Corp');
        $response->assertSee('Pune, India');
        $response->assertSee('Lead CI/CD pipeline automation');
        $response->assertSee('Extensive knowledge of Docker');
        $response->assertSee('₹14,00,000 - ₹19,00,000 / yr');
    }

    /**
     * Test 4: Authenticated user can apply for a job.
     */
    public function test_authenticated_user_can_apply_for_a_job(): void
    {
        $candidate = User::factory()->create([
            'name'  => 'Priya Candidate',
            'email' => 'priya.candidate@example.com',
        ]);

        $job = Job::factory()->create([
            'title'   => 'Backend API Engineer',
            'company' => 'Apex Payments',
            'status'  => 'active',
        ]);

        $applicationData = [
            'cover_letter' => 'I have 2 years of experience designing Sanctum REST APIs and comprehensive PHPUnit test suites.',
            'resume_url'   => 'https://linkedin.com/in/priya-candidate',
        ];

        $response = $this->actingAs($candidate)->post(route('jobs.apply', $job), $applicationData);

        $response->assertStatus(302);
        $response->assertRedirect(route('jobs.show', $job));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('applications', [
            'job_id'       => $job->id,
            'user_id'      => $candidate->id,
            'cover_letter' => $applicationData['cover_letter'],
            'resume_url'   => $applicationData['resume_url'],
            'status'       => 'pending',
        ]);

        // Verifying relationship traversal
        $this->assertTrue($candidate->applications()->where('job_id', $job->id)->exists());
        $this->assertTrue($job->applications()->where('user_id', $candidate->id)->exists());
    }

    /**
     * Test 5: Duplicate application from the same user to the same job is strictly prevented.
     */
    public function test_duplicate_application_is_prevented(): void
    {
        $candidate = User::factory()->create([
            'email' => 'unique.applicant@example.com',
        ]);

        $job = Job::factory()->create([
            'title'  => 'AI Integration Engineer',
            'status' => 'active',
        ]);

        // First application: Successful
        $firstResponse = $this->actingAs($candidate)->post(route('jobs.apply', $job), [
            'cover_letter' => 'Initial application submission.',
            'resume_url'   => 'https://github.com/applicant-one',
        ]);

        $firstResponse->assertStatus(302);
        $firstResponse->assertSessionHas('success');
        $this->assertDatabaseCount('applications', 1);

        // Second application by SAME user to SAME job: Rejected
        $duplicateResponse = $this->actingAs($candidate)->post(route('jobs.apply', $job), [
            'cover_letter' => 'Attempting a duplicate submission.',
            'resume_url'   => 'https://github.com/applicant-one-duplicate',
        ]);

        $duplicateResponse->assertStatus(302);
        $duplicateResponse->assertSessionHas('error', 'You have already submitted an application for this position.');

        // Total count in database MUST remain exactly 1
        $this->assertDatabaseCount('applications', 1);
    }

    /**
     * Test 6: Authenticated dashboard displays recruitment metrics.
     */
    public function test_authenticated_dashboard_displays_recruitment_metrics(): void
    {
        $user = User::factory()->create();

        // Create 2 jobs posted by user, 1 job posted by someone else
        Job::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Job::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        $otherJob = Job::factory()->create(['status' => 'active']);

        // User applies to the other job
        Application::factory()->create([
            'job_id'  => $otherJob->id,
            'user_id' => $user->id,
            'status'  => 'reviewed',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Internship Workspace Dashboard');
        $response->assertSee('Capstone Recruitment Portal');
        $response->assertSee('Available Jobs');
        $response->assertSee('Jobs Posted');
        $response->assertSee('Applications');
    }

    /**
     * Test 7: Candidate can view their submitted applications history.
     */
    public function test_candidate_can_view_submitted_applications(): void
    {
        $candidate = User::factory()->create();
        $job = Job::factory()->create([
            'title'   => 'Cloud Security Architect',
            'company' => 'SafeHarbor Networks',
        ]);

        Application::factory()->create([
            'job_id'  => $job->id,
            'user_id' => $candidate->id,
            'status'  => 'shortlisted',
        ]);

        $response = $this->actingAs($candidate)->get(route('applications.index'));

        $response->assertStatus(200);
        $response->assertSee('My Job Applications');
        $response->assertSee('Cloud Security Architect');
        $response->assertSee('SafeHarbor Networks');
        $response->assertSee('shortlisted');
    }

    /**
     * Test 8: REST API endpoints return proper JSON resources and prevent duplicate applications.
     */
    public function test_rest_api_endpoints_work_with_sanctum(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->create([
            'title'   => 'API Microservices Lead',
            'company' => 'FinTech Scale',
            'status'  => 'active',
        ]);

        // A. Public GET /api/jobs
        $listResponse = $this->getJson('/api/jobs');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'company', 'location', 'employment_type', 'status'],
            ],
        ]);

        // B. Public GET /api/jobs/{job}
        $showResponse = $this->getJson("/api/jobs/{$job->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.title', 'API Microservices Lead');

        // C. Protected POST /api/jobs/{job}/apply with Sanctum
        Sanctum::actingAs($user);

        $applyResponse = $this->postJson("/api/jobs/{$job->id}/apply", [
            'cover_letter' => 'REST API candidate submission.',
            'resume_url'   => 'https://github.com/candidate',
        ]);

        $applyResponse->assertStatus(201);
        $applyResponse->assertJsonPath('data.status', 'pending');

        // D. Protected POST duplicate apply returns 422
        $duplicateApiResponse = $this->postJson("/api/jobs/{$job->id}/apply", [
            'cover_letter' => 'Duplicate REST API submission.',
        ]);

        $duplicateApiResponse->assertStatus(422);
        $duplicateApiResponse->assertJsonPath('message', 'You have already submitted an application for this position.');

        // E. Protected GET /api/user/applications
        $userAppsResponse = $this->getJson('/api/user/applications');
        $userAppsResponse->assertStatus(200);
        $userAppsResponse->assertJsonCount(1, 'data');
    }
}
