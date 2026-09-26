<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Senior Laravel Engineer',
            'Full Stack PHP & Vue Developer',
            'Cloud Infrastructure & DevOps Engineer',
            'Frontend React & Tailwind Specialist',
            'REST API Integration Architect',
            'Junior Backend Laravel Developer',
            'Database Administrator & Performance Tuning Specialist',
            'Technical Product Manager',
            'QA Automation Engineer (PHPUnit & Cypress)',
            'Cybersecurity & IAM Engineer',
            'AI Solutions & ML Pipeline Engineer',
            'Mobile Application Engineer (Flutter & Laravel API)',
        ];

        $companies = [
            'Cynaris Systems Inc.',
            'NovaCore Cloud Labs',
            'HyperScale Technologies',
            'Apex Data Innovations',
            'Vanguard Financial Software',
            'Summit Digital Group',
            'Acuity HealthTech',
            'Starlight Interactive',
        ];

        $locations = [
            'Bengaluru, India (Hybrid)',
            'Pune, India (On-site)',
            'Hyderabad, India (Flexible)',
            'Remote (Global / India friendly)',
            'Mumbai, India (Hybrid)',
            'San Francisco, CA (Remote)',
            'London, UK (Remote Eligible)',
        ];

        $types = ['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'];

        $salaries = [
            '₹12,00,000 - ₹18,00,000 / yr',
            '₹18,00,000 - ₹26,00,000 / yr',
            '₹8,00,000 - ₹12,00,000 / yr',
            '$90,000 - $125,000 / yr',
            '$120,000 - $155,000 / yr',
            '₹4,50,000 - ₹7,00,000 / yr (Internship stipend included)',
            'Competitive with equity options',
        ];

        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement($titles),
            'company' => fake()->randomElement($companies),
            'location' => fake()->randomElement($locations),
            'employment_type' => fake()->randomElement($types),
            'description' => "We are looking for a skilled and motivated engineer to join our high-impact product engineering group. You will lead development sprints, architect reliable backend domain services, write maintainable tests, and collaborate with cross-functional stakeholders.\n\nKey Responsibilities:\n- Design, develop, and maintain performant web applications and RESTful APIs.\n- Ensure code quality, high automated test coverage, and documentation standards.\n- Collaborate closely with UI/UX engineers and product owners on product roadmaps.\n- Optimize SQL database queries, indexing, and Redis caching layers.",
            'requirements' => "Qualifications & Skills:\n- 2+ years of hands-on experience building web applications using Laravel, PHP 8+, and MySQL/SQLite.\n- Solid understanding of Eloquent ORM, database migrations, model relationships, and query optimization.\n- Familiarity with Tailwind CSS, Vite, and modern JavaScript frontend workflows.\n- Experience writing automated unit and feature tests with PHPUnit or Pest.\n- Proficiency with Git version control, branch workflows, and CI/CD pipelines.",
            'salary_range' => fake()->randomElement($salaries),
            'application_deadline' => fake()->dateTimeBetween('+15 days', '+60 days')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
