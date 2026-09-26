@extends('layouts.app')

@section('title', 'Edit ' . $job->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 font-medium">
        <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a>
        <span>&rsaquo;</span>
        <a href="{{ route('jobs.index') }}" class="hover:text-indigo-600 transition">Job Board</a>
        <span>&rsaquo;</span>
        <a href="{{ route('jobs.show', $job) }}" class="hover:text-indigo-600 transition truncate">{{ $job->title }}</a>
        <span>&rsaquo;</span>
        <span class="text-gray-800 font-semibold">Edit</span>
    </nav>

    {{-- Error Banner --}}
    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            <h4 class="font-bold text-sm text-red-900">Please correct the following errors before submitting:</h4>
            <ul class="mt-2 text-xs text-red-800 space-y-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-10 shadow-sm">
        <div class="border-b border-gray-100 pb-6 mb-8 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Edit Opportunity</span>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mt-1">
                    Update Job Listing
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Modify the details of your posted opening.
                </p>
            </div>

            <a href="{{ route('jobs.show', $job) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                &larr; Back to Job
            </a>
        </div>

        <form action="{{ route('jobs.update', $job) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Job Title --}}
                <div class="md:col-span-2">
                    <label for="title" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Job Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="title"
                           id="title"
                           value="{{ old('title', $job->title) }}"
                           required
                           class="w-full px-4 py-2.5 bg-gray-50 border @error('title') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    @error('title')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Company Name --}}
                <div>
                    <label for="company" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Hiring Company <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="company"
                           id="company"
                           value="{{ old('company', $job->company) }}"
                           required
                           class="w-full px-4 py-2.5 bg-gray-50 border @error('company') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    @error('company')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Location --}}
                <div>
                    <label for="location" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Location / Work Mode <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="location"
                           id="location"
                           value="{{ old('location', $job->location) }}"
                           required
                           class="w-full px-4 py-2.5 bg-gray-50 border @error('location') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    @error('location')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Employment Type --}}
                <div>
                    <label for="employment_type" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Employment Type <span class="text-red-500">*</span>
                    </label>
                    <select name="employment_type" id="employment_type" required class="w-full px-4 py-2.5 bg-gray-50 border @error('employment_type') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        @foreach (['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'] as $type)
                            <option value="{{ $type }}" {{ old('employment_type', $job->employment_type) === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                    @error('employment_type')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Salary Range --}}
                <div>
                    <label for="salary_range" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Compensation / Salary Range
                    </label>
                    <input type="text"
                           name="salary_range"
                           id="salary_range"
                           value="{{ old('salary_range', $job->salary_range) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border @error('salary_range') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    @error('salary_range')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Application Deadline --}}
                <div>
                    <label for="application_deadline" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Application Deadline
                    </label>
                    <input type="date"
                           name="application_deadline"
                           id="application_deadline"
                           value="{{ old('application_deadline', $job->application_deadline?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border @error('application_deadline') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    @error('application_deadline')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Publication Status
                    </label>
                    <select name="status" id="status" class="w-full px-4 py-2.5 bg-gray-50 border @error('status') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        <option value="active" {{ old('status', $job->status) === 'active' ? 'selected' : '' }}>Active (Accepting Applications)</option>
                        <option value="draft" {{ old('status', $job->status) === 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                        <option value="closed" {{ old('status', $job->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                    @error('status')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Job Description --}}
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Role Overview & Responsibilities <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description"
                              id="description"
                              rows="6"
                              required
                              class="w-full px-4 py-2.5 bg-gray-50 border @error('description') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none leading-relaxed">{{ old('description', $job->description) }}</textarea>
                    @error('description')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Qualifications & Requirements --}}
                <div class="md:col-span-2">
                    <label for="requirements" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                        Candidate Qualifications & Requirements <span class="text-red-500">*</span>
                    </label>
                    <textarea name="requirements"
                              id="requirements"
                              rows="5"
                              required
                              class="w-full px-4 py-2.5 bg-gray-50 border @error('requirements') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none leading-relaxed">{{ old('requirements', $job->requirements) }}</textarea>
                    @error('requirements')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('jobs.show', $job) }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-sm transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
