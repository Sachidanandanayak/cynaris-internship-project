@extends('layouts.app')

@section('title', $title)

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 0.5rem;">{{ $title }}</h1>
        <p style="color: var(--text-muted);">
            Demonstration of Laravel POST requests, CSRF token verification (<code>@csrf</code>), server-side input validation, and flash sessions.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <strong>Success!</strong> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('submitted_data'))
        <div class="card" style="background: #f0fdf4; border-color: #86efac; margin-bottom: 2rem;">
            <h3 style="color: #166534; font-size: 1.1rem; margin-bottom: 0.5rem;">Submitted Payload (Safely Rendered)</h3>
            <p style="color: #14532d; font-size: 0.9rem; margin-bottom: 0.75rem;">
                All user inputs below are sanitized and escaped using Blade's default <code>&lcub;&lcub; $var &rcub;&rcub;</code> syntax:
            </p>
            <ul style="color: #166534; font-size: 0.9rem; margin-left: 1.25rem;">
                <li><strong>Name:</strong> {{ session('submitted_data.name') }}</li>
                <li><strong>Email:</strong> {{ session('submitted_data.email') }}</li>
                <li><strong>Department:</strong> {{ session('submitted_data.department') }}</li>
                <li><strong>Message:</strong> {{ session('submitted_data.message') }}</li>
            </ul>
        </div>
    @endif

    <div class="card">
        <h2>Send a Message</h2>
        <form action="{{ route('form.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Full Name *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Jane Doe"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="jane@example.com"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="department" class="form-label">Department *</label>
                <select
                    id="department"
                    name="department"
                    class="form-control @error('department') is-invalid @enderror"
                    required
                >
                    <option value="">-- Choose a Department --</option>
                    @foreach ($departments as $key => $label)
                        <option value="{{ $key }}" {{ old('department') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('department')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="message" class="form-label">Message *</label>
                <textarea
                    id="message"
                    name="message"
                    rows="4"
                    class="form-control @error('message') is-invalid @enderror"
                    placeholder="Enter your message or questions here..."
                    required
                >{{ old('message') }}</textarea>
                @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button type="submit" class="btn btn-primary">
                    Submit Message (POST)
                </button>
                <a href="{{ route('form.index') }}" class="btn btn-outline">
                    Reset Form
                </a>
            </div>
        </form>
    </div>
@endsection
