<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Internship Workspace Dashboard') }}
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
            <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl text-white shadow-lg">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-black">Welcome back, {{ Auth::user()->name }}!</h3>
                        <p class="mt-1 text-indigo-100 text-sm">
                            You have successfully authenticated into the Cynaris Internship portal. This route is secured by Laravel's <code>auth</code> and <code>verified</code> middleware.
                        </p>
                    </div>
                    <div class="shrink-0 flex gap-3">
                        <a href="{{ route('account') }}" class="btn" style="background-color: #ffffff; color: #4f46e5;">
                            Account Security &rarr;
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn" style="background-color: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
                    <span class="text-xs uppercase font-semibold text-gray-400">Account User</span>
                    <div class="text-lg font-bold text-gray-900 mt-1 truncate">{{ Auth::user()->name }}</div>
                    <span class="text-xs text-gray-500">{{ Auth::user()->email }}</span>
                </div>

                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
                    <span class="text-xs uppercase font-semibold text-gray-400">Registered Phone</span>
                    <div class="text-lg font-bold text-indigo-600 mt-1 truncate">
                        {{ Auth::user()->phone ?? 'N/A' }}
                    </div>
                    <span class="text-xs text-gray-500">Week 4 Custom Field</span>
                </div>

                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
                    <span class="text-xs uppercase font-semibold text-gray-400">Email Verification</span>
                    <div class="text-lg font-bold text-emerald-600 mt-1 flex items-center gap-1">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Verified</span>
                    </div>
                    <span class="text-xs text-gray-500">MustVerifyEmail Active</span>
                </div>

                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
                    <span class="text-xs uppercase font-semibold text-gray-400">Session Security</span>
                    <div class="text-lg font-bold text-gray-900 mt-1">Active (CSRF + Web)</div>
                    <span class="text-xs text-gray-500">Regenerated on Login</span>
                </div>
            </div>

            <!-- Features Showcase -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                <h4 class="text-base font-bold text-gray-900 mb-2">Week 3 & Week 4 Integrated Navigation</h4>
                <p class="text-sm text-gray-600 mb-4">
                    All Week 3 modules remain fully operational with seamless session preservation:
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('home') }}" class="pill">Home (Day 2)</a>
                    <a href="{{ route('about') }}" class="pill">About (Day 2)</a>
                    <a href="{{ route('form.index') }}" class="pill">Form (Day 2)</a>
                    <a href="{{ route('products.index') }}" class="pill">Products CRUD (Day 3)</a>
                    <a href="{{ route('database.demo') }}" class="pill">Database Demo (Day 4)</a>
                    <a href="{{ route('blog.index') }}" class="pill">Blog CRUD (Day 5)</a>
                    <a href="{{ route('account') }}" class="pill active">Account Security (Week 4)</a>
                    <a href="{{ route('profile.edit') }}" class="pill">Profile Settings (Week 4)</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
