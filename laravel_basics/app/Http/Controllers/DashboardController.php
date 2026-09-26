<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Render the unified authenticated user & recruitment dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Capstone Dashboard Metrics
        $availableJobsCount = Job::where('status', 'active')->count();
        $jobsPostedCount = Job::where('user_id', $user->id)->count();
        $applicationsCount = Application::where('user_id', $user->id)->count();

        // Recent items
        $recentJobs = Job::where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        $recentApplications = Application::with('job.user')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $userJobs = Job::where('user_id', $user->id)
            ->withCount('applications')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'availableJobsCount',
            'jobsPostedCount',
            'applicationsCount',
            'recentJobs',
            'recentApplications',
            'userJobs'
        ));
    }
}
