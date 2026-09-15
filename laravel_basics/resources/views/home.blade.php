@extends('layouts.app')

@section('title', $title)

@section('content')
    <section class="hero">
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('about', ['topic' => 'internship']) }}" class="btn" style="background-color: #ffffff; color: var(--primary);">
                Explore Modules &rarr;
            </a>
            <a href="{{ route('form.index') }}" class="btn" style="background-color: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                Try Feedback Form
            </a>
        </div>
    </section>

    <section class="card">
        <h2>Core Concepts Demonstrated Today</h2>
        <p style="color: var(--text-muted); margin-bottom: 1rem;">
            This page is rendered by <code>HomeController@index</code>. The features below were passed dynamically from the controller to this Blade view using PHP's <code>compact()</code> helper.
        </p>

        <div class="features-grid">
            @foreach ($frameworkFeatures as $feature)
                <div class="feature-card">
                    <span class="feature-badge">{{ $feature['badge'] }}</span>
                    <h3>{{ $feature['title'] }}</h3>
                    <p>{{ $feature['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card" style="background: #fdfbf7; border-color: #fed7aa;">
        <h3 style="color: #9a3412; margin-bottom: 0.5rem; font-size: 1.1rem;">Quick Route Tester</h3>
        <p style="color: #7c2d12; font-size: 0.95rem; margin-bottom: 1rem;">
            Test the parameterized GET route directly with these module parameters:
        </p>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('about', 'internship') }}" class="pill">Topic: Internship</a>
            <a href="{{ route('about', 'mvc') }}" class="pill">Topic: MVC</a>
            <a href="{{ route('about', 'routing') }}" class="pill">Topic: Routing</a>
            <a href="{{ route('about', 'blade') }}" class="pill">Topic: Blade</a>
        </div>
    </section>
@endsection
