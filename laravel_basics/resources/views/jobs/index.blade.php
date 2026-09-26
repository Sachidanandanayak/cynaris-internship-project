@extends('layouts.app')

@section('title', 'Job Board & Recruitment Portal')

@section('content')
<div class="space-y-8">
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

    {{-- Capstone Hero Header --}}
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 text-white rounded-2xl p-8 sm:p-10 shadow-xl border border-indigo-700/40">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Cynaris Capstone Project &bull; Full-Stack Recruitment System
                </span>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white leading-tight">
                    Explore Career Opportunities & Hire Tech Talent
                </h1>
                <p class="mt-3 text-indigo-100/90 text-base sm:text-lg">
                    Discover verified software engineering roles, apply with one click, or publish job openings to find top talent.
                </p>

                {{-- Live Quick Stat Pills --}}
                <div class="mt-6 flex flex-wrap gap-3">
                    <div class="px-3.5 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm border border-white/15 text-xs font-medium flex items-center gap-2">
                        <span class="text-emerald-400 font-bold text-sm">{{ $stats['total_active'] }}</span> Active Positions
                    </div>
                    <div class="px-3.5 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm border border-white/15 text-xs font-medium flex items-center gap-2">
                        <span class="text-indigo-300 font-bold text-sm">{{ $stats['remote'] }}</span> Remote Roles
                    </div>
                    <div class="px-3.5 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm border border-white/15 text-xs font-medium flex items-center gap-2">
                        <span class="text-purple-300 font-bold text-sm">{{ $stats['full_time'] }}</span> Full-time
                    </div>
                    <div class="px-3.5 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm border border-white/15 text-xs font-medium flex items-center gap-2">
                        <span class="text-amber-300 font-bold text-sm">{{ $stats['internships'] }}</span> Internships
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                <a href="{{ route('jobs.create') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white text-indigo-900 font-bold hover:bg-indigo-50 shadow-md transition duration-150 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Post a Job Opening
                </a>
                @auth
                    <a href="{{ route('applications.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-indigo-700/60 hover:bg-indigo-700 text-white font-medium border border-indigo-400/30 transition duration-150 gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        My Applications
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Search and Filter Controls --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <form method="GET" action="{{ route('jobs.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            {{-- Search Keyword --}}
            <div class="md:col-span-5">
                <label for="search" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                    Keywords or Skills
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           id="search"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Role title, company, technology, or city..."
                           class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
            </div>

            {{-- Employment Type Filter --}}
            <div class="md:col-span-3">
                <label for="employment_type" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                    Employment Type
                </label>
                <select name="employment_type" id="employment_type" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    <option value="all">All Employment Types</option>
                    @foreach (['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'] as $type)
                        <option value="{{ $type }}" {{ ($filters['employment_type'] ?? '') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="md:col-span-2">
                <label for="status" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
                    Status
                </label>
                <select name="status" id="status" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    <option value="active" {{ ($filters['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="all" {{ ($filters['status'] ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            {{-- Filter Actions --}}
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition shadow-sm flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                </button>
                @if (!empty($filters['search']) || (isset($filters['employment_type']) && $filters['employment_type'] !== 'all') || (isset($filters['status']) && $filters['status'] !== 'active'))
                    <a href="{{ route('jobs.index') }}" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm transition" title="Clear Filters">
                        &times;
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Job Listings Count Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span>Available Positions</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                {{ $jobs->total() }}
            </span>
        </h2>
        <span class="text-xs text-gray-500">
            Showing {{ $jobs->firstItem() ?? 0 }}-{{ $jobs->lastItem() ?? 0 }} of {{ $jobs->total() }} listings
        </span>
    </div>

    {{-- Job Listings Cards Grid --}}
    @if ($jobs->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($jobs as $job)
                <div class="bg-white rounded-xl border border-gray-200 hover:border-indigo-300 hover:shadow-lg transition-all duration-200 flex flex-col justify-between p-6 group">
                    <div>
                        {{-- Top Meta: Company & Type Badge --}}
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider truncate">
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
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                {{ $job->employment_type }}
                            </span>
                        </div>

                        {{-- Job Title --}}
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition leading-snug">
                            <a href="{{ route('jobs.show', $job) }}">
                                {{ $job->title }}
                            </a>
                        </h3>

                        {{-- Location & Salary --}}
                        <div class="mt-3 space-y-1.5 text-xs text-gray-600">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="truncate">{{ $job->location }}</span>
                            </div>

                            @if ($job->salary_range)
                                <div class="flex items-center gap-1.5 font-semibold text-emerald-700">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $job->salary_range }}</span>
                                </div>
                            @endif

                            @if ($job->application_deadline)
                                <div class="flex items-center gap-1.5 text-gray-500">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Deadline: {{ $job->application_deadline->format('M d, Y') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Snippet --}}
                        <p class="mt-3.5 text-xs text-gray-600 line-clamp-3 leading-relaxed">
                            {{ Str::limit(strip_tags($job->description), 140) }}
                        </p>
                    </div>

                    {{-- Card Footer --}}
                    <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            {{ $job->applications_count }} {{ Str::plural('applicant', $job->applications_count) }}
                        </span>

                        <a href="{{ route('jobs.show', $job) }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                            <span>View Details</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $jobs->links() }}
        </div>
    @else
        {{-- Professional Empty State --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 mx-auto rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No Job Opportunities Found</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                We couldn't find any positions matching your search filters. Try adjusting your keyword terms or exploring all employment types.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('jobs.index') }}" class="btn btn-outline text-sm">
                    Reset Search Filters
                </a>
                <a href="{{ route('jobs.create') }}" class="btn btn-primary text-sm">
                    Post a New Job
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
