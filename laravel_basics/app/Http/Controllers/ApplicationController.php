<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyJobRequest;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Display a listing of applications submitted by the authenticated user.
     */
    public function index(): View
    {
        $user = Auth::user();

        $applications = Application::with('job.user')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('applications.index', compact('applications'));
    }

    /**
     * Submit an application for a specific job.
     */
    public function store(ApplyJobRequest $request, Job $job): RedirectResponse
    {
        $userId = Auth::id();

        // 1. Check for duplicate application
        if (Application::where('job_id', $job->id)->where('user_id', $userId)->exists()) {
            return back()
                ->withInput()
                ->with('error', 'You have already submitted an application for this position.');
        }

        // 2. Prevent applying if job is not active
        if ($job->status !== 'active') {
            return back()
                ->with('error', 'This position is currently not accepting new applications.');
        }

        // 3. Create application
        Application::create([
            'job_id'       => $job->id,
            'user_id'      => $userId,
            'cover_letter' => $request->input('cover_letter'),
            'resume_url'   => $request->input('resume_url'),
            'status'       => 'pending',
        ]);

        return redirect()
            ->route('jobs.show', $job)
            ->with('success', "Your application for '{$job->title}' at {$job->company} was submitted successfully!");
    }
}
