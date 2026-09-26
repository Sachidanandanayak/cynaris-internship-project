<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApplyJobApiRequest;
use App\Http\Requests\Api\StoreJobApiRequest;
use App\Http\Requests\Api\UpdateJobApiRequest;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\JobResource;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobApiController extends Controller
{
    /**
     * Display a listing of jobs.
     *
     * GET /api/jobs
     * Status: 200 OK
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', 10);

        $jobs = Job::with('user')
            ->withCount('applications')
            ->filter($request->only(['search', 'employment_type', 'status']))
            ->latest()
            ->paginate($perPage);

        return JobResource::collection($jobs);
    }

    /**
     * Display the specified job.
     *
     * GET /api/jobs/{job}
     * Status: 200 OK (or 404 Not Found)
     */
    public function show(Job $job): JobResource
    {
        $job->load('user');
        $job->loadCount('applications');

        return new JobResource($job);
    }

    /**
     * Store a newly created job.
     *
     * POST /api/jobs (Sanctum protected)
     * Status: 201 Created (or 422 Unprocessable, 401 Unauthorized)
     */
    public function store(StoreJobApiRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $job = Job::create($data);
        $job->load('user');
        $job->loadCount('applications');

        return (new JobResource($job))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified job.
     *
     * PUT/PATCH /api/jobs/{job} (Sanctum protected)
     * Status: 200 OK (or 422 Unprocessable, 401 Unauthorized, 404 Not Found)
     */
    public function update(UpdateJobApiRequest $request, Job $job): JsonResponse
    {
        $job->update($request->validated());
        $job->load('user');
        $job->loadCount('applications');

        return (new JobResource($job))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified job.
     *
     * DELETE /api/jobs/{job} (Sanctum protected)
     * Status: 200 OK (or 401 Unauthorized, 404 Not Found)
     */
    public function destroy(Job $job): JsonResponse
    {
        $job->delete();

        return response()->json([
            'message' => 'Job listing deleted successfully.',
        ], 200);
    }

    /**
     * Apply for the specified job.
     *
     * POST /api/jobs/{job}/apply (Sanctum protected)
     * Status: 201 Created (or 422 Unprocessable/Duplicate, 401 Unauthorized)
     */
    public function apply(ApplyJobApiRequest $request, Job $job): JsonResponse
    {
        $userId = $request->user()->id;

        // Prevent duplicate applications
        if (Application::where('job_id', $job->id)->where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'You have already submitted an application for this position.',
                'errors'  => [
                    'application' => ['You have already submitted an application for this position.'],
                ],
            ], 422);
        }

        $application = Application::create([
            'job_id'       => $job->id,
            'user_id'      => $userId,
            'cover_letter' => $request->input('cover_letter'),
            'resume_url'   => $request->input('resume_url'),
            'status'       => 'pending',
        ]);

        $application->load(['job', 'user']);

        return (new ApplicationResource($application))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get the authenticated user's submitted job applications.
     *
     * GET /api/user/applications (Sanctum protected)
     * Status: 200 OK (or 401 Unauthorized)
     */
    public function userApplications(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', 10);

        $applications = Application::with(['job.user', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return ApplicationResource::collection($applications);
    }
}
