<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'user_id' => User::factory(),
            'cover_letter' => "Dear Hiring Team,\n\nI am writing to express my enthusiastic interest in this opportunity. With comprehensive experience in PHP, Laravel, and modern web application development, I have developed a strong track record of delivering clean, test-driven applications.\n\nThank you for considering my profile, and I look forward to discussing how my experience aligns with your team goals.",
            'resume_url' => 'https://linkedin.com/in/' . fake()->userName(),
            'status' => fake()->randomElement(['pending', 'reviewed', 'shortlisted']),
        ];
    }
}
