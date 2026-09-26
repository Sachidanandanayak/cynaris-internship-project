@extends('layouts.app')

@section('title', $title)

@section('content')
    <section class="hero">
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('jobs.index') }}" class="btn" style="background-color: #ffffff; color: #312e81; font-weight: 800; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15);">
                Capstone Job Board &rarr;
            </a>
            <a href="{{ route('about', ['topic' => 'internship']) }}" class="btn" style="background-color: rgba(255,255,255,0.2); color: #ffffff; border: 1px solid rgba(255,255,255,0.35);">
                Explore Modules &rarr;
            </a>
            <a href="{{ route('form.index') }}" class="btn" style="background-color: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                Try Feedback Form
            </a>
            <a href="{{ route('products.index') }}" class="btn" style="background-color: rgba(255,255,255,0.25); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); font-weight: 700;">
                Product CRUD (Day 3) &rarr;
            </a>
        </div>
    </section>

    {{-- Capstone Job Board Showcase --}}
    <section class="card" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff; border: none; padding: 2rem; border-radius: var(--radius);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
            <div>
                <span style="display: inline-block; background: rgba(255,255,255,0.15); color: #c7d2fe; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; margin-bottom: 0.6rem;">
                    WEEK 4 DAY 5 &bull; FINAL CAPSTONE PROJECT
                </span>
                <h2 style="color: #ffffff; font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                    Full-Stack Job Board & Recruitment System
                </h2>
                <p style="color: #e0e7ff; font-size: 0.95rem; max-width: 650px; line-height: 1.6;">
                    Enterprise recruitment workflow built with Laravel 13, Eloquent ORM, Sanctum API, and Blade. Browse verified openings, apply with custom cover notes, and manage candidate pipelines.
                </p>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('jobs.index') }}" class="btn" style="background: #ffffff; color: #312e81; font-weight: 700;">
                    Browse Open Roles &rarr;
                </a>
                <a href="{{ route('jobs.create') }}" class="btn" style="background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); font-weight: 600;">
                    Post a Job Opening
                </a>
            </div>
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
