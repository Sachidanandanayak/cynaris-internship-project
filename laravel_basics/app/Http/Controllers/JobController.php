<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobRequest;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JobController extends Controller
{
    /**
     * Display a listing of jobs with search, filtering, and pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'employment_type', 'status']);

        // Default to active jobs if status is not explicitly set
        if (! isset($filters['status']) || $filters['status'] === '') {
            $filters['status'] = 'active';
        }

        $jobs = Job::with('user')
            ->withCount('applications')
            ->filter($filters)
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $stats = [
            'total_active' => Job::where('status', 'active')->count(),
            'remote'       => Job::where('status', 'active')->where('employment_type', 'Remote')->count(),
            'full_time'    => Job::where('status', 'active')->where('employment_type', 'Full-time')->count(),
            'internships'  => Job::where('status', 'active')->where('employment_type', 'Internship')->count(),
        ];

        return view('jobs.index', compact('jobs', 'filters', 'stats'));
    }

    /**
     * Show the form for creating a new job listing.
     */
    public function create(): View
    {
        return view('jobs.create');
    }

    /**
     * Store a newly created job listing in storage.
     */
    public function store(JobRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['status'] = $data['status'] ?? 'active';

        $job = Job::create($data);

        return redirect()
            ->route('jobs.show', $job)
            ->with('success', "Job opportunity '{$job->title}' published successfully!");
    }

    /**
     * Display the specified job listing details.
     */
    public function show(Job $job): View
    {
        $job->load(['user', 'applications.user']);
        $job->loadCount('applications');

        $user = Auth::user();
        $hasApplied = $job->hasAppliedBy($user);
        $userApplication = $hasApplied
            ? $job->applications()->where('user_id', $user?->id)->first()
            : null;

        $isOwner = $user && $job->user_id === $user->id;

        // Similar active jobs
        $similarJobs = Job::where('id', '!=', $job->id)
            ->where('status', 'active')
            ->where(function ($q) use ($job) {
                $q->where('employment_type', $job->employment_type)
                    ->orWhere('company', $job->company);
            })
            ->take(3)
            ->get();

        return view('jobs.show', compact('job', 'hasApplied', 'userApplication', 'isOwner', 'similarJobs'));
    }

    /**
     * Show the form for editing the specified job listing.
     */
    public function edit(Job $job): View
    {
        // Enforce ownership: only the poster can edit
        if ($job->user_id !== Auth::id()) {
            abort(403, 'Unauthorized. You can only edit jobs posted by your account.');
        }

        return view('jobs.edit', compact('job'));
    }

    /**
     * Update the specified job listing in storage.
     */
    public function update(JobRequest $request, Job $job): RedirectResponse
    {
        if ($job->user_id !== Auth::id()) {
            abort(403, 'Unauthorized. You can only update jobs posted by your account.');
        }

        $job->update($request->validated());

        return redirect()
            ->route('jobs.show', $job)
            ->with('success', "Job listing '{$job->title}' updated successfully.");
    }

    /**
     * Remove the specified job listing from storage.
     */
    public function destroy(Job $job): RedirectResponse
    {
        if ($job->user_id !== Auth::id()) {
            abort(403, 'Unauthorized. You can only delete jobs posted by your account.');
        }

        $title = $job->title;
        $job->delete();

        return redirect()
            ->route('jobs.index')
            ->with('success', "Job listing '{$title}' has been successfully deleted.");
    }
}
