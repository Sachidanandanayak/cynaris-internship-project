@extends('layouts.app')

@section('title', 'My Job Applications')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">
                My Job Applications
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Track status updates and review applications submitted with your candidate profile.
            </p>
        </div>

        <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-sm self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Explore More Jobs
        </a>
    </div>

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

    {{-- Applications Table / List --}}
    @if ($applications->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="py-3.5 px-6">Job Opportunity</th>
                            <th class="py-3.5 px-6">Company</th>
                            <th class="py-3.5 px-6">Applied Date</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach ($applications as $application)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-900">
                                        <a href="{{ route('jobs.show', $application->job) }}" class="hover:text-indigo-600 transition">
                                            {{ $application->job->title }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                        <span>{{ $application->job->location }}</span>
                                        &bull;
                                        <span class="text-indigo-600 font-medium">{{ $application->job->employment_type }}</span>
                                    </div>
                                </td>

                                <td class="py-4 px-6 text-xs font-semibold text-gray-700">
                                    {{ $application->job->company }}
                                </td>

                                <td class="py-4 px-6 text-xs text-gray-500 whitespace-nowrap">
                                    {{ $application->created_at->format('M d, Y') }}
                                    <span class="block text-[11px] text-gray-400">({{ $application->created_at->diffForHumans() }})</span>
                                </td>

                                <td class="py-4 px-6 whitespace-nowrap">
                                    @php
                                        $statusClass = match(strtolower($application->status)) {
                                            'accepted', 'shortlisted' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'reviewed'                => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'rejected'                => 'bg-red-100 text-red-800 border-red-200',
                                            default                   => 'bg-amber-100 text-amber-800 border-amber-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusClass }}">
                                        {{ $application->status }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <a href="{{ route('jobs.show', $application->job) }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                        <span>View Details</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $applications->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 mx-auto rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No Applications Submitted Yet</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                You haven't applied to any job openings so far. Browse our curated listings and submit your profile with a customized cover note.
            </p>
            <a href="{{ route('jobs.index') }}" class="btn btn-primary text-sm px-6 py-2.5">
                Browse Available Jobs
            </a>
        </div>
    @endif
</div>
@endsection
