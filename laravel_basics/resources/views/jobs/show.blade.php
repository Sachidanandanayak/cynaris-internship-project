@extends('layouts.app')

@section('title', $job->title . ' at ' . $job->company)

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb Navigation --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 font-medium">
        <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a>
        <span>&rsaquo;</span>
        <a href="{{ route('jobs.index') }}" class="hover:text-indigo-600 transition">Job Board</a>
        <span>&rsaquo;</span>
        <span class="text-gray-800 font-semibold truncate">{{ $job->title }}</span>
    </nav>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="alert alert-success flex items-center justify-between" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error flex items-center justify-between" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900 font-bold">&times;</button>
        </div>
    @endif

    {{-- Job Details Header Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">
                        {{ $job->company }}
                    </span>

                    @php
                        $badgeClass = match($job->employment_type) {
                            'Remote'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Full-time'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'Internship' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'Contract'   => 'bg-amber-50 text-amber-700 border-amber-200',
                            default      => 'bg-gray-50 text-gray-700 border-gray-200',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                        {{ $job->employment_type }}
                    </span>

                    @if ($job->status === 'active')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            Active Role
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                            {{ ucfirst($job->status) }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 leading-tight">
                    {{ $job->title }}
                </h1>

                <div class="flex flex-wrap items-center gap-y-2 gap-x-5 text-sm text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ $job->location }}</span>
                    </div>

                    @if ($job->salary_range)
                        <div class="flex items-center gap-1.5 font-semibold text-emerald-700">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $job->salary_range }}</span>
                        </div>
                    @endif

                    @if ($job->application_deadline)
                        <div class="flex items-center gap-1.5 text-gray-500">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Apply by {{ $job->application_deadline->format('F d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Owner / Recruiter Actions --}}
            @if ($isOwner)
                <div class="flex flex-wrap lg:flex-col gap-2 shrink-0">
                    <a href="{{ route('jobs.edit', $job) }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-bold transition border border-indigo-200">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Job Listing
                    </a>

                    <form action="{{ route('jobs.destroy', $job) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this job listing?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-xs font-bold transition border border-red-200">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Job
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Two Column Content Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Left: Role Details & Application --}}
        <div class="lg:col-span-8 space-y-8">
            {{-- Job Description Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4">
                        About the Role & Responsibilities
                    </h2>
                    <div class="prose max-w-none text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                        {{ $job->description }}
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4">
                        Qualifications & Requirements
                    </h2>
                    <div class="prose max-w-none text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                        {{ $job->requirements }}
                    </div>
                </div>
            </div>

            {{-- Application Submission / Status Section --}}
            <div id="apply-section" class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 mb-2">
                    Application Process
                </h2>

                @if ($hasApplied)
                    {{-- User already applied --}}
                    <div class="mt-4 p-5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-emerald-900">Application Submitted!</h3>
                                <p class="text-xs text-emerald-700 mt-0.5">
                                    You successfully applied for this position on {{ $userApplication->created_at->format('M d, Y \a\t h:i A') }}.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-emerald-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-emerald-800">Current Review Status:</span>
                                <span class="px-2.5 py-1 rounded-full font-bold uppercase tracking-wider text-[11px] bg-emerald-200 text-emerald-900">
                                    {{ $userApplication->status }}
                                </span>
                            </div>

                            <a href="{{ route('applications.index') }}" class="font-bold text-emerald-800 hover:text-emerald-950 underline">
                                View in My Applications &rarr;
                            </a>
                        </div>

                        @if ($userApplication->cover_letter)
                            <div class="mt-4 pt-3 border-t border-emerald-100 text-xs text-emerald-800">
                                <span class="font-semibold block mb-1">Your Submitted Cover Note:</span>
                                <p class="italic bg-emerald-100/50 p-3 rounded-lg whitespace-pre-line">{{ $userApplication->cover_letter }}</p>
                            </div>
                        @endif
                    </div>

                @elseif ($job->status !== 'active')
                    {{-- Position Closed --}}
                    <div class="mt-4 p-5 rounded-xl bg-gray-50 border border-gray-200 text-gray-700">
                        <p class="text-sm font-semibold">This job opening is currently closed to new applicants.</p>
                        <p class="text-xs text-gray-500 mt-1">Please browse our other active listings.</p>
                    </div>

                @elseif (Auth::check())
                    {{-- Authenticated Candidate Application Form --}}
                    <p class="text-sm text-gray-600 mb-6">
                        Submit your details directly to <strong>{{ $job->company }}</strong>. One application per candidate is permitted.
                    </p>

                    <form action="{{ route('jobs.apply', $job) }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label for="applicant_name" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                Candidate Name & Email
                            </label>
                            <input type="text"
                                   id="applicant_name"
                                   disabled
                                   value="{{ Auth::user()->name }} ({{ Auth::user()->email }})"
                                   class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-600 cursor-not-allowed">
                            <span class="text-[11px] text-gray-500 mt-1 block">Submitting through your verified Cynaris profile.</span>
                        </div>

                        <div>
                            <label for="resume_url" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Resume or Portfolio Link <span class="text-gray-400 font-normal lowercase">(optional)</span>
                            </label>
                            <input type="url"
                                   name="resume_url"
                                   id="resume_url"
                                   value="{{ old('resume_url') }}"
                                   placeholder="https://linkedin.com/in/yourprofile or https://github.com/yourhandle"
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border @error('resume_url') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            @error('resume_url')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="cover_letter" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Cover Note & Qualifications
                            </label>
                            <textarea name="cover_letter"
                                      id="cover_letter"
                                      rows="5"
                                      placeholder="Briefly describe why your experience, technical skills, and background make you a great fit for this position..."
                                      class="w-full px-3.5 py-2.5 bg-gray-50 border @error('cover_letter') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none leading-relaxed">{{ old('cover_letter') }}</textarea>
                            @error('cover_letter')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Submit Job Application
                        </button>
                    </form>

                @else
                    {{-- Guest Callout --}}
                    <div class="mt-4 p-6 rounded-xl bg-indigo-50/70 border border-indigo-200 text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-base text-indigo-950 mb-1">Apply for this Position</h3>
                        <p class="text-xs text-indigo-800 max-w-sm mx-auto mb-4">
                            You must be authenticated to submit an application. Sign in to your existing account or create a candidate profile in seconds.
                        </p>
                        <div class="flex justify-center gap-3">
                            <a href="{{ route('login') }}" class="btn btn-primary text-xs px-5 py-2.5">
                                Log In to Apply
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline text-xs px-5 py-2.5 bg-white">
                                Register Candidate Account
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Recruiter Candidate Review Section (Owner Only) --}}
            @if ($isOwner)
                <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-2 flex items-center justify-between">
                        <span>Candidate Submissions</span>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-800 font-bold">
                            {{ $job->applications->count() }} Total
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 mb-6">
                        Review applicants who have submitted proposals for this job listing.
                    </p>

                    @if ($job->applications->count() > 0)
                        <div class="divide-y divide-gray-100">
                            @foreach ($job->applications as $application)
                                <div class="py-4 space-y-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <h4 class="font-bold text-sm text-gray-900">
                                                {{ $application->user->name ?? 'Candidate' }}
                                            </h4>
                                            <span class="text-xs text-gray-500">
                                                {{ $application->user->email ?? 'N/A' }} &bull; Applied {{ $application->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            Status: {{ ucfirst($application->status) }}
                                        </span>
                                    </div>

                                    @if ($application->resume_url)
                                        <div class="text-xs">
                                            <span class="font-semibold text-gray-700">Portfolio/Resume:</span>
                                            <a href="{{ $application->resume_url }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline">
                                                {{ $application->resume_url }} &nearr;
                                            </a>
                                        </div>
                                    @endif

                                    @if ($application->cover_letter)
                                        <div class="p-3 bg-gray-50 rounded-lg text-xs text-gray-700 italic whitespace-pre-line border border-gray-100">
                                            {{ $application->cover_letter }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center text-xs text-gray-500 bg-gray-50 rounded-xl">
                            No candidate applications submitted for this role yet.
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right Sidebar: Job Summary & Similar Positions --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Summary Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-900 border-b border-gray-100 pb-3">
                    Position Overview
                </h3>

                <dl class="space-y-3 text-xs">
                    <div>
                        <dt class="text-gray-400 font-semibold uppercase">Company</dt>
                        <dd class="font-bold text-gray-900 text-sm mt-0.5">{{ $job->company }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-400 font-semibold uppercase">Employment Type</dt>
                        <dd class="font-medium text-gray-800 mt-0.5">{{ $job->employment_type }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-400 font-semibold uppercase">Location</dt>
                        <dd class="font-medium text-gray-800 mt-0.5">{{ $job->location }}</dd>
                    </div>

                    @if ($job->salary_range)
                        <div>
                            <dt class="text-gray-400 font-semibold uppercase">Compensation</dt>
                            <dd class="font-bold text-emerald-700 text-sm mt-0.5">{{ $job->salary_range }}</dd>
                        </div>
                    @endif

                    @if ($job->application_deadline)
                        <div>
                            <dt class="text-gray-400 font-semibold uppercase">Application Deadline</dt>
                            <dd class="font-medium text-gray-800 mt-0.5">{{ $job->application_deadline->format('M d, Y') }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-gray-400 font-semibold uppercase">Posted On</dt>
                        <dd class="font-medium text-gray-800 mt-0.5">{{ $job->created_at->format('M d, Y') }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-400 font-semibold uppercase">Posted By</dt>
                        <dd class="font-medium text-gray-800 mt-0.5">{{ $job->user->name ?? 'Cynaris Recruiter' }}</dd>
                    </div>
                </dl>

                @if (!$hasApplied && $job->status === 'active')
                    <div class="pt-4 border-t border-gray-100">
                        <a href="#apply-section" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition shadow-sm">
                            Apply Now
                        </a>
                    </div>
                @endif
            </div>

            {{-- Similar Jobs Card --}}
            @if ($similarJobs->count() > 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-gray-900 border-b border-gray-100 pb-3">
                        Similar Openings
                    </h3>

                    <div class="divide-y divide-gray-100">
                        @foreach ($similarJobs as $similar)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">
                                    {{ $similar->company }}
                                </span>
                                <h4 class="font-bold text-xs text-gray-900 hover:text-indigo-600 transition mt-0.5">
                                    <a href="{{ route('jobs.show', $similar) }}">
                                        {{ $similar->title }}
                                    </a>
                                </h4>
                                <div class="mt-1 flex items-center justify-between text-[11px] text-gray-500">
                                    <span>{{ $similar->location }}</span>
                                    <span class="font-medium text-indigo-600">{{ $similar->employment_type }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
