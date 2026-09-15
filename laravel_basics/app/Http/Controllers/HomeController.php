<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the application home page.
     * Demonstrates passing data to Blade view using compact().
     */
    public function index(): View
    {
        $title = 'Laravel Basics - Week 3 Day 2';
        $subtitle = 'Welcome to the Cynaris Internship Week 3 Day 2 Laravel Demonstration.';
        $frameworkFeatures = [
            [
                'title' => 'Routing Architecture',
                'description' => 'Clean RESTful routing with GET & POST endpoints and route parameter handling.',
                'badge' => 'routes/web.php',
            ],
            [
                'title' => 'Blade Templating Engine',
                'description' => 'Reusable layouts demonstrating @extends, @section, @yield, and @include directives.',
                'badge' => 'Blade Directives',
            ],
            [
                'title' => 'Controller Data Flow',
                'description' => 'Passing dynamic arrays and scalar data to views cleanly using the compact() helper.',
                'badge' => 'compact() Helper',
            ],
            [
                'title' => 'Form Validation & CSRF',
                'description' => 'Secure POST requests protected by @csrf tokens with robust server-side validation.',
                'badge' => 'Security & State',
            ],
        ];

        return view('home', compact('title', 'subtitle', 'frameworkFeatures'));
    }

    /**
     * Display the about page with an optional topic parameter.
     * Demonstrates route parameters and compact() data passing.
     */
    public function about(?string $topic = 'internship'): View
    {
        $topicKey = strtolower($topic ?? 'internship');

        $topics = [
            'internship' => 'Cynaris Web Development Internship program covering foundational PHP, modern Laravel MVC architecture, and professional practices.',
            'mvc' => 'Model-View-Controller pattern separating application data (Models), user interface (Views), and business flow (Controllers).',
            'routing' => 'Mapping incoming HTTP requests to dedicated controller actions with parameter capture, named routing, and middleware support.',
            'blade' => 'Laravel templating engine compiling views into plain cached PHP code for zero-overhead performance.',
        ];

        $topicDetails = $topics[$topicKey] ?? "Custom parameter received: '$topicKey'. Explore our standard modules via the topic links below.";
        $formattedTopic = ucfirst($topicKey);
        $title = "About - {$formattedTopic}";

        return view('about', compact('title', 'formattedTopic', 'topicKey', 'topicDetails', 'topics'));
    }
}
