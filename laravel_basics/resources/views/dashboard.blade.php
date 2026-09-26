<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Internship Workspace Dashboard — Capstone Recruitment Portal') }}
            </h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                <span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full"></span>
                Authenticated & Verified (auth, verified)
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome Hero -->
            <div class="p-6 sm:p-8 bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-2xl text-white shadow-xl border border-indigo-700/40">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-indigo-200 border border-white/15 mb-2">
                            Capstone Full-Stack Job Board &bull; Recruitment Hub
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black">Welcome back, {{ Auth::user()->name }}!</h3>
                        <p class="mt-2 text-indigo-100/90 text-sm max-w-2xl leading-relaxed">
                            Manage your job postings, track candidate submissions, and explore verified engineering opportunities from your unified dashboard.
                        </p>
                    </div>
                    <div class="shrink-0 flex flex-wrap gap-3">
                        <a href="{{ route('jobs.create') }}" class="btn" style="background-color: #ffffff; color: #3730a3; font-weight: 700;">
                            + Post a Job
                        </a>
                        <a href="{{ route('jobs.index') }}" class="btn" style="background-color: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                            Browse Jobs &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Capstone Recruitment & Account Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Metric 1: Available Jobs --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs uppercase font-bold text-gray-400">Available Jobs</span>
                        <div class="text-2xl font-black text-indigo-600 mt-1">
                            {{ $availableJobsCount ?? 0 }}
                        </div>
                        <span class="text-[11px] text-gray-500">Live active listings</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Metric 2: Jobs Posted by User --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs uppercase font-bold text-gray-400">Jobs Posted</span>
                        <div class="text-2xl font-black text-purple-600 mt-1">
                            {{ $jobsPostedCount ?? 0 }}
                        </div>
                        <span class="text-[11px] text-gray-500">Managed by your account</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Metric 3: Applications Submitted --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs uppercase font-bold text-gray-400">Applications</span>
                        <div class="text-2xl font-black text-emerald-600 mt-1">
                            {{ $applicationsCount ?? 0 }}
                        </div>
                        <span class="text-[11px] text-gray-500">Submitted by your profile</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Metric 4: Account Security --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs uppercase font-bold text-gray-400">Registered User</span>
                        <div class="text-sm font-bold text-gray-900 mt-1 truncate max-w-[150px]">
                            {{ Auth::user()->email }}
                        </div>
                        <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Breeze Auth & Verified
                        </span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Two Column Activity Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Column 1: Your Submitted Applications --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                            <h4 class="font-bold text-base text-gray-900 flex items-center gap-2">
                                <span>Recent Applications</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    {{ $applicationsCount ?? 0 }}
                                </span>
                            </h4>
                            <a href="{{ route('applications.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                View All &rarr;
                            </a>
                        </div>

                        @if (isset($recentApplications) && $recentApplications->count() > 0)
                            <div class="divide-y divide-gray-100">
                                @foreach ($recentApplications as $app)
                                    <div class="py-3 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                                        <div>
                                            <h5 class="font-bold text-xs text-gray-900 hover:text-indigo-600 transition">
                                                <a href="{{ route('jobs.show', $app->job) }}">
                                                    {{ $app->job->title }}
                                                </a>
                                            </h5>
                                            <span class="text-[11px] text-gray-500">
                                                {{ $app->job->company }} &bull; Applied {{ $app->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        @php
                                            $appBadgeClass = match(strtolower($app->status)) {
                                                'accepted', 'shortlisted' => 'bg-emerald-100 text-emerald-800',
                                                'reviewed'                => 'bg-blue-100 text-blue-800',
                                                'rejected'                => 'bg-red-100 text-red-800',
                                                default                   => 'bg-amber-100 text-amber-800',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider shrink-0 {{ $appBadgeClass }}">
                                            {{ $app->status }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-gray-500 text-xs">
                                <p>You haven't submitted any job applications yet.</p>
                                <a href="{{ route('jobs.index') }}" class="mt-2 inline-block font-bold text-indigo-600 hover:underline">
                                    Explore Active Positions &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Column 2: Jobs Posted By You --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                            <h4 class="font-bold text-base text-gray-900 flex items-center gap-2">
                                <span>Jobs Posted by You</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                    {{ $jobsPostedCount ?? 0 }}
                                </span>
                            </h4>
                            <a href="{{ route('jobs.create') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                + Post New
                            </a>
                        </div>

                        @if (isset($userJobs) && $userJobs->count() > 0)
                            <div class="divide-y divide-gray-100">
                                @foreach ($userJobs as $ujob)
                                    <div class="py-3 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                                        <div>
                                            <h5 class="font-bold text-xs text-gray-900 hover:text-indigo-600 transition">
                                                <a href="{{ route('jobs.show', $ujob) }}">
                                                    {{ $ujob->title }}
                                                </a>
                                            </h5>
                                            <span class="text-[11px] text-gray-500">
                                                {{ $ujob->employment_type }} &bull; {{ $ujob->applications_count }} {{ Str::plural('applicant', $ujob->applications_count) }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <a href="{{ route('jobs.edit', $ujob) }}" class="text-[11px] font-bold text-gray-600 hover:text-indigo-600">
                                                Edit
                                            </a>
                                            <span class="text-gray-300">|</span>
                                            <a href="{{ route('jobs.show', $ujob) }}" class="text-[11px] font-bold text-indigo-600 hover:underline">
                                                View &rarr;
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-gray-500 text-xs">
                                <p>You haven't posted any job listings yet.</p>
                                <a href="{{ route('jobs.create') }}" class="mt-2 inline-block font-bold text-indigo-600 hover:underline">
                                    Create Your First Job Posting &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Live Jobs Across Platform -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h4 class="font-bold text-base text-gray-900">
                        Latest Verified Openings
                    </h4>
                    <a href="{{ route('jobs.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                        View All {{ $availableJobsCount ?? 0 }} Jobs &rarr;
                    </a>
                </div>

                @if (isset($recentJobs) && $recentJobs->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($recentJobs->take(3) as $rJob)
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-white hover:border-indigo-200 transition space-y-2">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-gray-500 uppercase truncate">{{ $rJob->company }}</span>
                                    <span class="font-semibold text-indigo-600">{{ $rJob->employment_type }}</span>
                                </div>
                                <h5 class="font-bold text-xs text-gray-900">
                                    <a href="{{ route('jobs.show', $rJob) }}" class="hover:text-indigo-600 transition">
                                        {{ $rJob->title }}
                                    </a>
                                </h5>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 pt-1">
                                    <span>{{ $rJob->location }}</span>
                                    <a href="{{ route('jobs.show', $rJob) }}" class="font-bold text-indigo-600 hover:underline">
                                        Details &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Features Showcase (Week 3 & Week 4 Integrated Modules Preserved) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <h4 class="text-base font-bold text-gray-900 mb-1">Cynaris Internship Integrated Application Suite</h4>
                <p class="text-xs text-gray-500 mb-4">
                    All foundational curriculum modules remain fully active and accessible:
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('jobs.index') }}" class="pill active" style="font-weight: 700;">Job Board (Capstone)</a>
                    <a href="{{ route('home') }}" class="pill">Home (Day 2)</a>
                    <a href="{{ route('about') }}" class="pill">About (Day 2)</a>
                    <a href="{{ route('form.index') }}" class="pill">Form (Day 2)</a>
                    <a href="{{ route('products.index') }}" class="pill">Products CRUD (Day 3)</a>
                    <a href="{{ route('database.demo') }}" class="pill">Database Demo (Day 4)</a>
                    <a href="{{ route('blog.index') }}" class="pill">Blog CRUD (Day 5)</a>
                    <a href="{{ route('api.demo') }}" class="pill">API Demo (Week 4)</a>
                    <a href="{{ route('debug.demo') }}" class="pill">Debug Demo (Week 4)</a>
                    <a href="{{ route('account') }}" class="pill">Account Security (Week 4)</a>
                    <a href="{{ route('profile.edit') }}" class="pill">Profile Settings (Week 4)</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
