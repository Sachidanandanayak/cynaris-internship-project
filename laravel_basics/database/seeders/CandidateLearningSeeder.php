<?php

namespace Database\Seeders;

use App\Models\CandidateLesson;
use App\Models\QuizScore;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CandidateLearningSeeder extends Seeder
{
    /**
     * Seed realistic completed lessons and quiz scores for candidates.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            // Create a default intern candidate user if none exists
            $user = User::create([
                'name' => 'Sachidananda Nayak',
                'email' => '23BTCE160@gcu.edu.in',
                'phone' => '+91 9876543210',
                'password' => bcrypt('Password123!'),
                'email_verified_at' => now(),
            ]);
            $users = collect([$user]);
        }

        foreach ($users as $user) {
            // Avoid duplicate seeding
            if ($user->candidateLessons()->count() > 0) {
                continue;
            }

            $now = Carbon::now();

            $lessonsData = [
                // PHP
                ['title' => 'PHP 8 Fundamentals & Type System', 'skill' => 'PHP', 'category' => 'Backend Core', 'score' => 92.0, 'days_ago' => 20],
                ['title' => 'Object-Oriented PHP & Design Patterns', 'skill' => 'PHP', 'category' => 'Backend Core', 'score' => 88.0, 'days_ago' => 18],
                ['title' => 'Error Handling & Exception Architecture in PHP', 'skill' => 'PHP', 'category' => 'Backend Core', 'score' => 85.0, 'days_ago' => 16],

                // Laravel
                ['title' => 'Laravel MVC Architecture & Routing', 'skill' => 'Laravel', 'category' => 'Framework', 'score' => 95.0, 'days_ago' => 15],
                ['title' => 'Eloquent ORM: Relationships & Migrations', 'skill' => 'Laravel', 'category' => 'Framework', 'score' => 90.0, 'days_ago' => 14],
                ['title' => 'Form Requests, Validation & Middleware', 'skill' => 'Laravel', 'category' => 'Framework', 'score' => 92.0, 'days_ago' => 12],
                ['title' => 'Laravel Breeze & Session Authentication', 'skill' => 'Laravel', 'category' => 'Framework', 'score' => 89.0, 'days_ago' => 10],

                // MySQL / Database
                ['title' => 'Relational Database Schema Design & Normalization', 'skill' => 'MySQL', 'category' => 'Database', 'score' => 84.0, 'days_ago' => 13],
                ['title' => 'SQL Query Optimization & Indexing Strategies', 'skill' => 'MySQL', 'category' => 'Database', 'score' => 78.0, 'days_ago' => 11],

                // REST APIs
                ['title' => 'RESTful API Standards & HTTP Status Codes', 'skill' => 'REST APIs', 'category' => 'API & Integration', 'score' => 94.0, 'days_ago' => 9],
                ['title' => 'Laravel Sanctum Token Authentication', 'skill' => 'REST APIs', 'category' => 'API & Integration', 'score' => 91.0, 'days_ago' => 8],

                // JavaScript
                ['title' => 'Modern JavaScript (ES6+), DOM & Async Programming', 'skill' => 'JavaScript', 'category' => 'Frontend', 'score' => 82.0, 'days_ago' => 7],
                ['title' => 'AJAX Fetch & Reactive UI State Management', 'skill' => 'JavaScript', 'category' => 'Frontend', 'score' => 80.0, 'days_ago' => 5],

                // Git & CI/CD
                ['title' => 'Git Feature Branch Workflow & Upstream Rebasing', 'skill' => 'Git & CI/CD', 'category' => 'DevOps & Tooling', 'score' => 88.0, 'days_ago' => 6],
                ['title' => 'Automated Testing with PHPUnit & GitHub Actions', 'skill' => 'Git & CI/CD', 'category' => 'DevOps & Tooling', 'score' => 85.0, 'days_ago' => 4],

                // Docker (Foundational entry)
                ['title' => 'Introduction to Containerization & Dockerfile Basics', 'skill' => 'Docker', 'category' => 'DevOps & Tooling', 'score' => 60.0, 'days_ago' => 2],
            ];

            foreach ($lessonsData as $data) {
                CandidateLesson::create([
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'skill' => $data['skill'],
                    'category' => $data['category'],
                    'is_completed' => true,
                    'score' => $data['score'],
                    'completed_at' => (clone $now)->subDays($data['days_ago']),
                ]);
            }

            $quizzesData = [
                ['quiz_title' => 'PHP 8 Advanced Syntax & OOP Mastery Quiz', 'skill' => 'PHP', 'score' => 46.0, 'max_score' => 50.0, 'percentage' => 92.0, 'passed' => true, 'days_ago' => 17],
                ['quiz_title' => 'Laravel Eloquent & Blade Architecture Challenge', 'skill' => 'Laravel', 'score' => 48.0, 'max_score' => 50.0, 'percentage' => 96.0, 'passed' => true, 'days_ago' => 11],
                ['quiz_title' => 'Database Normalization & Indexing Quiz', 'skill' => 'MySQL', 'score' => 39.0, 'max_score' => 50.0, 'percentage' => 78.0, 'passed' => true, 'days_ago' => 10],
                ['quiz_title' => 'REST API Design & Sanctum Security Quiz', 'skill' => 'REST APIs', 'score' => 45.0, 'max_score' => 50.0, 'percentage' => 90.0, 'passed' => true, 'days_ago' => 7],
                ['quiz_title' => 'JavaScript Async/Await & Event Loop Quiz', 'skill' => 'JavaScript', 'score' => 40.0, 'max_score' => 50.0, 'percentage' => 80.0, 'passed' => true, 'days_ago' => 4],
                ['quiz_title' => 'Docker Multi-Stage Builds & Networking Quiz', 'skill' => 'Docker', 'score' => 27.0, 'max_score' => 50.0, 'percentage' => 54.0, 'passed' => false, 'days_ago' => 1],
            ];

            foreach ($quizzesData as $data) {
                QuizScore::create([
                    'user_id' => $user->id,
                    'quiz_title' => $data['quiz_title'],
                    'skill' => $data['skill'],
                    'score' => $data['score'],
                    'max_score' => $data['max_score'],
                    'percentage' => $data['percentage'],
                    'passed' => $data['passed'],
                    'attempted_at' => (clone $now)->subDays($data['days_ago']),
                ]);
            }
        }
    }
}
