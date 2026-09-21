<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Overview & Security') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- User Profile Summary Card -->
            <div class="p-6 bg-white shadow sm:rounded-lg">
                <div class="flex items-center justify-between border-b pb-4 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Personal Information</h3>
                        <p class="text-sm text-gray-600">Your registered contact and authentication credentials.</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                        Protected Route: /account
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <span class="text-xs uppercase font-semibold text-gray-500">Full Name</span>
                        <p class="text-base font-bold text-gray-900 mt-1" id="account-user-name">{{ $user->name }}</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <span class="text-xs uppercase font-semibold text-gray-500">Email Address</span>
                        <p class="text-base font-bold text-gray-900 mt-1" id="account-user-email">{{ $user->email }}</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <span class="text-xs uppercase font-semibold text-gray-500">Phone Number (Week 4 Custom Field)</span>
                        <p class="text-base font-bold text-indigo-700 mt-1" id="account-user-phone">
                            {{ $user->phone ?? 'Not provided' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Email Verification Status & Security -->
            <div class="p-6 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Email Verification Status</h3>
                <p class="text-sm text-gray-600 mb-4">Verification guarantees the authenticity of your account and unlocks verified-only routes like <code>/dashboard</code>.</p>

                <div class="flex items-center gap-4">
                    @if ($user->hasVerifiedEmail())
                        <div class="flex items-center px-4 py-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800">
                            <svg class="w-5 h-5 mr-2 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-semibold text-sm">Verified on {{ $user->email_verified_at->format('M d, Y H:i') }}</span>
                        </div>
                    @else
                        <div class="flex items-center px-4 py-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800">
                            <svg class="w-5 h-5 mr-2 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-semibold text-sm">Pending Email Verification</span>
                        </div>
                        <a href="{{ route('verification.notice') }}" class="btn btn-primary btn-sm">
                            Go to Verification Notice &rarr;
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="p-6 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-3">Authenticated Navigation</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        Dashboard (Verified)
                    </a>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline">
                        Edit Profile
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline">
                        Products (Week 3)
                    </a>
                    <a href="{{ route('blog.index') }}" class="btn btn-outline">
                        Blog CRUD (Week 3)
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
